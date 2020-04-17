<?php

namespace App;

use App\Console\Console;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as ErrorRunner;

final class App
{
    public function __construct()
    {
        self::setErrorHandler();
        if ('cli' === PHP_SAPI) {
            /* @phan-suppress-next-line PhanNoopNew */
            new Console();
        }
    }

    private static function setErrorHandler(): void
    {
        $logger = new Logger('app_error');
        $logger->pushHandler(new ErrorLogHandler());

        $handler = new PlainTextHandler($logger);
        $handler->loggerOnly(true);

        $runner = new ErrorRunner();
        $runner->pushHandler($handler);

        if ('dev' === getenv('APP_ENV')) {
            $runner->pushHandler(
                isset($_SERVER['HTTP_ACCEPT']) && false !== strpos($_SERVER['HTTP_ACCEPT'], 'application/json')
                    ? new JsonResponseHandler()
                    : new PrettyPageHandler()
            );
        }

        $runner->register();
    }
}
