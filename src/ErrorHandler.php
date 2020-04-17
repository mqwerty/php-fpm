<?php

namespace App;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as ErrorRunner;

class ErrorHandler
{
    public function __construct()
    {
        $logger = new Logger('app_error');
        $logger->pushHandler(new ErrorLogHandler());

        $handler = new PlainTextHandler($logger);
        $handler->loggerOnly(true);

        $runner = new ErrorRunner();
        $runner->pushHandler($handler);

        if ('cli' !== PHP_SAPI && 'dev' === getenv('APP_ENV')) {
            $runner->pushHandler(
                isset($_SERVER['HTTP_ACCEPT']) && false !== strpos($_SERVER['HTTP_ACCEPT'], 'application/json')
                    ? new JsonResponseHandler()
                    : new PrettyPageHandler()
            );
        }

        $runner->register();
    }
}
