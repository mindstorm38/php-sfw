<?php

namespace SFW\Route\Middleware;

use SFW\Route\Route;

class SharedMiddleware {

    private $middleware;
    private $identifier;

    public function __construct(Middleware $middleware, string $identifier) {

        $this->middleware = $middleware;
        $this->identifier = "shared_{$identifier}";

    }

    public function get_middleware() : Middleware {
        return $this->middleware;
    }

    public function get_identifier() : string {
        return $this->identifier;
    }

    public function can_add_to(string $route_id, Route $route) : bool {
        return true;
    }

    public function add_to(Route $route) {
        $route->add_middleware($this->identifier, $this->middleware);
    }

}