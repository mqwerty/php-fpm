<?php

namespace App;

use Dev\ErrorHandler;
use Mqwerty\DI\Container;

class App
{
    protected static Container $container;

    /**
     * @suppress PhanUndeclaredClassReference
     * @suppress PhanUndeclaredClassMethod
     * @suppress PhanMissingRequireFile
     * @noinspection PhpIncludeInspection
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // In dev enviroment convert php errors to exceptions (including notice)
        // In prod enviroment see `docker logs`
        if (class_exists(ErrorHandler::class)) {
            (new ErrorHandler())->register();
        }
        $configInitial = file_exists('./config.initial.php') ? require './config.initial.php' : [];
        $configLocal = file_exists('./config.php') ? require './config.php' : [];
        $config = array_merge($configInitial, $configLocal, $config);
        static::$container = new Container($config);
    }

    public function run(): void
    {
        $handler = 'cli' === PHP_SAPI ? Console::class : Router::class;
        static::$container->get($handler)->handle();
    }
}
