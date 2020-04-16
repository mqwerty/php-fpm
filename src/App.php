<?php

namespace App;

use App\Command\Example;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Symfony\Component\Console\Application;
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
            self::setConsoleCommands();
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

    private static function setConsoleCommands(): void
    {
        $console = new Application();

        if (getenv('APP_ENV') === 'prod') {
            $console->setCatchExceptions(false);
        }

        $console->add(new Example());

        /** @noinspection PhpUnhandledExceptionInspection */
        $console->run();
    }
}
