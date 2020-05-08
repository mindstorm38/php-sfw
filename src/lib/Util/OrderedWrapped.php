<?php

namespace SFW\Util;

final class OrderedWrapped implements Ordered {

	private $order;
	private $wrapped;

	public function __construct(int $order, $wrapped) {

		$this->order = $order;
		$this->wrapped = $wrapped;

	}

	public function get_order(): int {
		return $this->order;
	}

	public function get_wrapped() {
		return $this->wrapped;
	}

}