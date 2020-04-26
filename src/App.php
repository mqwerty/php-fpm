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
        /** @phan-suppress-next-line PhanUndeclaredClassReference */
        if (class_exists(ErrorHandler::class)) {
            ErrorHandler::register(); /** @phan-suppress-current-line PhanUndeclaredClassMethod */
        }
        self::setContainer();
    }

    public function run(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        'cli' === PHP_SAPI ? Console::handle() : Router::handle();
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
