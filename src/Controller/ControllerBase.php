<?php

namespace App\Controller;

use FastRoute\RouteCollector;

interface ControllerBase
{
    public static function routes(RouteCollector $r): void;
}
