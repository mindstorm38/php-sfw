<?php

namespace SFW\Route\Middleware\Shared;

use SFW\Route\Middleware\Middleware;
use SFW\Route\Route;

class FilterIdSharedMiddleware extends SharedMiddleware {

    private $inclusive = false;
    private $ids = [];

    public function __construct(Middleware $middleware, string $identifier, int $order, bool $inclusive = false) {
        parent::__construct($middleware, $identifier, $order);
        $this->inclusive = $inclusive;
    }

    public function is_inclusive(): bool {
        return $this->inclusive;
    }

    public function is_exclusive(): bool {
        return !$this->inclusive;
    }

    public function can_add_to(string $route_id, Route $route) : bool {
        return !$this->inclusive xor in_array($route_id, $this->ids);
    }

    public function add_route_id(string $id) {
        if (!in_array($id, $this->ids)) {
            $this->ids[] = $id;
        }
    }

    public function rem_route_id(string $id) {
        $idx = array_search($id, $this->ids);
        if ($idx !== false) {
            array_splice($this->ids, $idx, 1);
        }
    }

}