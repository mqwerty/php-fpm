<?php

namespace App;

use Dev\ErrorHandler;
use DI\Container;
use DI\ContainerBuilder;

final class App
{
    private static Container $container;

    public function __construct()
    {
        // In dev enviroment convert php errors to exceptions (including notice)
        // In prod enviroment see `docker logs`
        if (class_exists(ErrorHandler::class)) {
            ErrorHandler::register();
        }
        self::setContainer();
    }

    public function run(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        self::isHTTP() ? Router::handle() : Console::handle();
    }

    private static function setContainer(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        self::$container = (new ContainerBuilder())
            ->addDefinitions([])
            ->build();
    }

    public static function isHTTP(): bool
    {
        return 'true' === getenv('RR_HTTP');
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
