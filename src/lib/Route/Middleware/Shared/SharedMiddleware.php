<?php

namespace SFW\Route\Middleware\Shared;

use SFW\Route\Middleware\Middleware;
use SFW\Route\Route;

class SharedMiddleware {

    private $middleware;
    private $identifier;
    private $order;

    public function __construct(Middleware $middleware, string $identifier, int $order) {

        $this->middleware = $middleware;
        $this->identifier = "shared_{$identifier}";
        $this->order = $order;

    }

    public function get_middleware() : Middleware {
        return $this->middleware;
    }

    public function get_identifier() : string {
        return $this->identifier;
    }

    public function get_order(): int {
    	return $this->order;
    }

    public function can_add_to(string $route_id, Route $route) : bool {
        return true;
    }

    public function add_to(Route $route) {
        $route->add_middleware($this->identifier, $this->order, $this->middleware);
    }

}