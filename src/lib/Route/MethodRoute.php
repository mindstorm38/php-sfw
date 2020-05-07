<?php

namespace SFW\Route;

abstract class MethodRoute extends Route {

    protected $method;

    public function __construct(?string $method) {
        parent::__construct();
        $this->method = $method === null ? null : strtoupper($method);
    }

    public function get_method() : string {
        return $this->method;
    }

    public function valid_method(string $method) {
        return is_null($this->method) || $this->method === $method;
    }

    public function routable(string $method, string $path, string $bpath) : ?array {
        return $this->valid_method($method) ? $this->method_routable($path, $bpath) : null;
    }

    public abstract function method_routable(string $path, string $bpath) : ?array;

}