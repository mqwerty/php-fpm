<?php

namespace App;

use DI\Container;
use DI\ContainerBuilder;

final class App
{
    private static Container $container;

    public function __construct()
    {
        // prod - use `docker logs` and fluentd
        // dev - convert php errors to exceptions
        if ('prod' !== self::getEnv()) {
            ErrorHandler::register();
        }
        self::setContainer();
    }

    public function run(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        false === getenv('RR_HTTP')
            ? Console::handle()
            : Router::handle();
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

    public static function getRouter(): Router
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return self::get('router');
    }
}
