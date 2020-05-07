<?php

namespace SFW\Route;

use SFW\Core;
use SFW\QueryManager;
use \ArgumentCountError;
use SFW\Route\Middleware\Middleware;
use SFW\Util\OrderedTable;

abstract class Route {
	
	const ROUTE_PAGE = "page";
	const ROUTE_RES = "res";
	const ROUTE_API = "api";

	private $action = null;
	private $middlewares;

	public function __construct() {
	    $this->middlewares = new OrderedTable();
    }

    public function set_action(callable $action ): Route {
		
		$this->action = $action;
		return $this;
		
	}
	
	public function call_action(array $vars): bool {
		
		if ( $this->action === null ) {
			return false;
		}
		
		try {
			return ($this->action)(...$vars) ?? true;
		} catch (ArgumentCountError $e) {
			return false;
		}
		
	}
	
	public abstract function routable(string $method, string $path, string $bpath) : ?array;

	public function add_middleware(string $id, Middleware $mw) {
	    $this->middlewares->add($id, $mw);
    }

    public function rem_middleware(string $id) : bool {
	    return $this->middlewares->remove($id);
    }

    public function has_middleware(string $id) : bool {
	    return $this->middlewares->has($id);
    }

    public function build_middleware_chain(array $vars): callable {

	    $self = $this;

        $next = function() use ($self, &$vars) {
            $self->call_action($vars);
        };

	    $mws = $this->middlewares->get_sorted_list();
	    $count = count($mws);

	    for ($i = ($count - 1); $i >= 0; --$i) {

	        $mw = $mws[$i];

            $next = function() use ($mw, $self, &$vars, $next) {
                $mw->run($self, $vars, $next);
            };

        }

	    return $next;

    }

	// Predefined callbacks
	
	/**
	 * Create an action used to print page, variables returned by route are passed to {@link Core::print_page}.
	 * @param string $page The page ID printed.
	 * @return callable The action.
	 * @see Core::print_page
	 */
	public static function action_print_page(string $page): callable {
		
		return function(...$vars) use ($page) {
			Core::print_page($page, $vars);
		};
		
	}

    /**
     * Create an action used to print an error page (using {@link Core::print_error_page}).
     * @param int $code The error code.
     * @param string|null $message An optional message for error page.
     * @return callable The action.
     * @see Core::print_error_page
     */
	public static function action_print_error_page(int $code, ?string $message = null): callable {

        return function() use ($code, $message) {
            Core::print_error_page($code, $message);
        };

    }
	
	/**
	 * Create an action that call {@link Core::send_static_resource}.
	 * @return callable The action.
	 * @see Core::send_static_resource
	 */
	public static function action_send_static_resource(): callable {
		
		return function(string $path) {
			Core::send_static_resource($path);
		};
		
	}
	
	/**
	 * Create a controller to send query response using specified {@link QueryManager}.
	 * @param QueryManager $manager The query manager you want to use.
	 * @return callable The action.
	 * @see QueryManager
	 * @see QueryManager::send_query_response
	 */
	public static function action_send_query_response(QueryManager $manager): callable {
		
		return function(string $name) use ($manager) {
			$manager->send_query_response($name, $_POST);
		};
		
	}
	
}