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
        $handler = static::isHTTP() ? Router::class : Console::class;
        $this->container->get($handler)->handle();
    }

    public static function isHTTP(): bool
    {
        return 'true' === getenv('RR_HTTP');
    }

    /**
     * @noinspection PhpFullyQualifiedNameUsageInspection
     * @SuppressWarnings(PHPMD.MissingImport)
     * @param mixed $val
     */
    public static function dump($val): void
    {
        if (class_exists(\Spiral\Debug\Dumper::class)) {
            $dumper = new \Spiral\Debug\Dumper();
            $dumper->setRenderer(\Spiral\Debug\Dumper::ERROR_LOG, new \Spiral\Debug\Renderer\ConsoleRenderer());
            $dumper->dump($val, \Spiral\Debug\Dumper::ERROR_LOG);
        }
    }
}
