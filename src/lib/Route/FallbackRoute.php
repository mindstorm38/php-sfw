<?php

namespace SFW\Route;

class FallbackRoute extends Route {

    public function routable(string $method, string $path, string $bpath): ?array {
        return [];
    }

}