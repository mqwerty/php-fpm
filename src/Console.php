<?php

namespace App;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

final class Console
{
    public static function dispatch(): void
    {
        $app = new Application();

        if ('prod' === App::getEnv()) {
            $app->setCatchExceptions(false);
        }

        $app->setCommandLoader(new FactoryCommandLoader(self::commands()));

        /** @noinspection PhpUnhandledExceptionInspection */
        $app->run();
    }

    private static function commands(): array
    {
        return [
            Command\Example::getDefaultName() => fn() => new Command\Example(),
        ];
    }
}
