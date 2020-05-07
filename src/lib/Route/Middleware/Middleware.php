<?php

namespace SFW\Route\Middleware;

use SFW\Route\Route;
use SFW\Util\Ordered;

abstract class Middleware implements Ordered {

    private $order;

    public function __construct(int $order) {
        $this->order = $order;
    }

    public function get_order() : int {
        return $this->order;
    }

    public abstract function run(Route $route, array &$args, callable $next);

}

?>