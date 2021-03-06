<?php

namespace Dev;

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as ErrorRunner;

class ErrorHandler
{
    public function register(): void
    {
        (new ErrorRunner())
            ->pushHandler($this->getHandler())
            ->register();
    }

    protected function getHandler() {
        if ('cli' === PHP_SAPI) {
            return new PlainTextHandler();
        }
        if (isset($_SERVER['HTTP_ACCEPT']) && false !== strpos($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            return new JsonResponseHandler();
        }
        return new PrettyPageHandler();
    }
}
