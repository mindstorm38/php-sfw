<?php

namespace SFW\Route;

use SFW\Util\Ordered;

final class WrappedRoute implements Ordered {

    private $identifier;
    private $order;
    private $route;

    public function __construct(string $identifier, int $order, Route $route) {

        $this->identifier = $identifier;
        $this->order = $order;
        $this->route = $route;

    }

    public function get_identifier(): string {
        return $this->identifier;
    }

    public function get_order(): int {
        return $this->order;
    }

    public function get_route(): Route {
        return $this->route;
    }

}