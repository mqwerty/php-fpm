<?php

namespace Dev;

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as ErrorRunner;

class ErrorHandler
{
    public static function register(): void
    {
        if ('cli' !== PHP_SAPI) {
            (new ErrorRunner())
                ->pushHandler(
                    isset($_SERVER['HTTP_ACCEPT']) && false !== strpos($_SERVER['HTTP_ACCEPT'], 'application/json')
                        ? new JsonResponseHandler()
                        : new PrettyPageHandler()
                )
                ->register();
        }
    }
}
