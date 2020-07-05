<?php

use App\Factory\CommandLoaderFactory;
use App\Factory\DispatcherFactory;
use FastRoute\Dispatcher;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

return [
    'env' => 'dev', // prod | dev
    'logLevel' => 'debug',
    'routerCache' => '/tmp/app.router.cache',
    'consoleCache' => '/tmp/app.console.cache',
    'consoleLogEx' => false,
    'shared' => [
        Container::class,
        LoggerInterface::class,
    ],
    LoggerInterface::class => static function ($c) {
        $stream = defined('STDERR')
            ? new StreamHandler(STDERR, $c->get('logLevel'))
            : new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $c->get('logLevel'));
        return (new Logger('app'))->pushHandler($stream);
    },
    Dispatcher::class => [DispatcherFactory::class, 'getInstance'],
    CommandLoaderInterface::class => [CommandLoaderFactory::class, 'getInstance'],
    ContainerInterface::class => fn($c) => $c,
];
