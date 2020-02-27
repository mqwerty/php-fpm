<?php

namespace App;

use Dotenv\Dotenv;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as ErrorHandler;

final class App
{
    public function __construct()
    {
        self::setEnv();
        self::setErrorHandler();
    }

    private static function setEnv(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..', 'default.env');
        $dotenv->load();
        $dotenv->required('APP_ENV')->allowedValues(['prod', 'dev']);
    }

    private static function setErrorHandler(): void
    {
        if (getenv('APP_ENV') === 'dev') {
            (new ErrorHandler())
                ->pushHandler(new PrettyPageHandler())
                ->register();
        }
    }

    public function run(): void
    {
        echo Task::run();
    }
}
