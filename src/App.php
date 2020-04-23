<?php

namespace App;

use App\Console\Console;
use DI\Container;
use DI\ContainerBuilder;

final class App
{
    private static Container $container;

    /**
     * @phan-suppress PhanNoopNew
     */
    public function __construct()
    {
        new ErrorHandler();
        self::setContainer();
    }

    public function run(): void
    {
        'cli' === PHP_SAPI
            ? new Console()
            : Router::dispatch();
    }

    private static function setContainer(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        self::$container = (new ContainerBuilder())
            ->addDefinitions([])
            ->build();
    }

    public static function getEnv(): string
    {
        return getenv('APP_ENV') ?: 'prod';
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public static function get(string $name)
    {
        return self::$container->get($name);
    }
}
