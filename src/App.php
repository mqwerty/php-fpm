<?php

namespace App;

use Dotenv\Dotenv;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as ErrorHandler;

final class App
{
    public function __construct()
    {
        self::loadDotEnv();
        self::setIni();
        self::setErrorHandler();
    }

    private static function loadDotEnv(): void
    {
        Dotenv::createImmutable(__DIR__ . '/..')->load();
    }

    private static function setIni(): void
    {
        if (getenv('APP_ENV') === 'dev') {
            error_reporting(E_ALL);
            ini_set('display_errors', true);
            ini_set('display_startup_errors', true);
            assert_options(ASSERT_ACTIVE, true);
            ini_set('opcache.validate_timestamps', true);
            ini_set('opcache.revalidate_freq', 1);
        }
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
        echo self::hello() . PHP_EOL;
    }

    public static function hello(): string
    {
        return 'Hello, World!';
    }
}
