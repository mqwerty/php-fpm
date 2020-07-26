<?php

namespace App\Factory;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;
use Symfony\Component\Finder\Finder;

class CommandLoaderFactory
{
    public static function getInstance(ContainerInterface $c): FactoryCommandLoader
    {
        return new FactoryCommandLoader(static::commands($c));
    }

    protected static function commands(ContainerInterface $c): array
    {
        $cachefile = $c->get('consoleCache');
        $cacheEnabled = 'dev' !== $c->get('env');

        if ($cacheEnabled && file_exists($cachefile)) {
            /** @noinspection PhpIncludeInspection */
            $cache = require $cachefile;
            return array_map(fn($class) => fn() => $c->get($class), $cache);
        }

        $finder = new Finder();
        $finder->files()->in('./src/Command');
        if (!$finder->hasResults()) {
            return [];
        }

        $cache = [];
        $commands = [];
        foreach ($finder as $file) {
            $class = '\\App\\Command\\' . $file->getFilenameWithoutExtension();
            if (class_exists($class) && is_subclass_of($class, Command::class)) {
                $commands[$class::getDefaultName()] = fn() => $c->get($class);
                if ($cacheEnabled) {
                    $cache[$class::getDefaultName()] = $class;
                }
            }
        }

        if ($cacheEnabled) {
            file_put_contents(
                $cachefile,
                '<?php return ' . var_export($cache, true) . ';'
            );
        }

        return $commands;
    }
}
