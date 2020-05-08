<?php

namespace SFW\Route\Middleware;

use SFW\Route\Route;

abstract class Middleware {

    public abstract function run(Route $route, array &$args, callable $next);

}