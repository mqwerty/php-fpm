<?php

namespace App;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

final class Console
{
    /**
     * @throws \Exception
     */
    public static function handle(): void
    {
        $app = new Application();
        if ('dev' !== App::getEnv()) {
            $app->setCatchExceptions(false);
        }
        $app->setCommandLoader(new FactoryCommandLoader(self::commands()));
        $app->run();
    }

    private static function commands(): array
    {
        return [
            Command\Example::getDefaultName() => fn() => new Command\Example(),
        ];
    }
}
