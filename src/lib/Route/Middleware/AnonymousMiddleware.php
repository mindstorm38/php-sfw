<?php

namespace SFW\Route\Middleware;

use SFW\Route\Route;
use \ArgumentCountError;

class AnonymousMiddleware extends Middleware {

    private $callback;

    public function __construct(int $order, callable $callback) {
        parent::__construct($order);
        $this->callback = $callback;
    }

    public function run(Route $route, array &$args, callable $next) {
        try {
            ($this->callback)($route, $args, $next);
        } catch (ArgumentCountError $ignored) {}
    }

}