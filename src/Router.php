<?php

namespace App;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

use function FastRoute\cachedDispatcher;

final class Router
{
    public static function dispatch(): void
    {
        $routeInfo = self::dispatcher()->dispatch($_SERVER['REQUEST_METHOD'], self::decodeUri());
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                break;
            case Dispatcher::FOUND:
                [, $handler, $args] = $routeInfo;
                $request = ServerRequestFactory::fromGlobals();
                $response = $handler($request, $args);
                (new SapiEmitter())->emit($response);
        }
    }

    private static function dispatcher(): Dispatcher
    {
        return cachedDispatcher(
            [self::class, 'routes'],
            [
                'cacheFile' => getenv('APP_ROUTE_CACHE') ?: '/tmp/app.route.cache',
                'cacheDisabled' => 'dev' === App::getEnv(),
            ]
        );
    }

    /**
     * Strip query string (?foo=bar) and decode URI
     * @return string
     */
    private static function decodeUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        return rawurldecode($uri);
    }

    public static function routes(RouteCollector $r): void
    {
        $r->addRoute(Action\Example::getMethods(), Action\Example::getPath(), [Action\Example::class, 'execute']);
    }
}
