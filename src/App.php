<?php

namespace App;

use Dev\ErrorHandler;
use Mqwerty\DI\Container;
use Mqwerty\DI\NotFoundException;

class App
{
    protected Container $container;

    /**
     * @suppress PhanUndeclaredClassReference
     * @suppress PhanUndeclaredClassMethod
     * @suppress PhanMissingRequireFile
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // In dev enviroment convert php errors to exceptions (including notice)
        // In prod enviroment see `docker logs`
        if (class_exists(ErrorHandler::class)) {
            (new ErrorHandler())->register();
        }
        $configInitial = file_exists('./config.dist.php') ? require './config.dist.php' : [];
        $configLocal = file_exists('./config.php') ? require './config.php' : [];
        $config = array_merge($configInitial, $configLocal, $config);
        $this->container = new Container($config);
    }

    /**
     * @throws NotFoundException
     */
    public function run(): void
    {
        $handler = 'cli' === PHP_SAPI ? Console::class : Router::class;
        $this->container->get($handler)->handle();
    }
}
