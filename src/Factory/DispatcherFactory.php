<?php

namespace App\Factory;

use App\Controller\ControllerBase;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Container\ContainerInterface;
use Symfony\Component\Finder\Finder;

use function FastRoute\cachedDispatcher;

class DispatcherFactory
{
    public static function getInstance(ContainerInterface $c): Dispatcher
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return cachedDispatcher(
            [static::class, 'definitions'],
            [
                'cacheFile' => $c->get('routerCache'),
                'cacheDisabled' => 'dev' === $c->get('env'),
            ]
        );
    }

    public static function definitions(RouteCollector $r): void
    {
        $finder = new Finder();
        $finder->files()->in('./src/Controller');
        if (!$finder->hasResults()) {
            return;
        }
        foreach ($finder as $file) {
            $class = '\\App\\Controller\\' . $file->getFilenameWithoutExtension();
            if (class_exists($class) && is_subclass_of($class, ControllerBase::class)) {
                $class::routes($r);
            }
        }
    }
}
