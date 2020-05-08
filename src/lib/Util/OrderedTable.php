<?php

namespace SFW\Util;

use \BadMethodCallException;

class OrderedTable {

    private $keyed = [];
    private $list = [];

    public function __construct() {}

    public function has(string $key) {
        return isset($this->keyed[$key]);
    }

    public function add(string $key, Ordered $obj) {

        if ($this->has($key)) {
            throw new BadMethodCallException("A route with this name already exists, remove it first.");
        }

        $this->keyed[$key] = $obj;

        $count = count($this->list);

        for ($i = 0; $i < $count; ++$i) {
            if ($this->list[$i]->get_order() >= $obj->get_order()) {
                array_splice($this->list, $i, 0, [$obj]);
                return;
            }
        }

        array_splice($this->list, $count, 0, [$obj]);

    }

    public function remove(string $key): bool {

        if (!$this->has($key)) {
            return false;
        }

        $obj = $this->keyed[$key];
        $idx = array_search($obj, $this->list);
        array_splice($this->list, $idx, 1);
        unset($this->keyed[$key]);

        return true;

    }

    public function each(callable $callback) {
        foreach ($this->keyed as $key => $obj) {
            ($callback)($key, $obj);
        }
    }

    public function each_sorted(callable $callback) {
        foreach ($this->list as $idx => $obj) {
            ($callback)($obj, $idx);
        }
    }

	public function get_keyed_table(): array {
		return $this->keyed;
	}

    public function get_sorted_list(): array {
        return $this->list;
    }

}