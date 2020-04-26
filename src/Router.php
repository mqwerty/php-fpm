<?php

namespace App;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function FastRoute\cachedDispatcher;

final class Router
{
    public static function handle(): void
    {
        $request = ServerRequestFactory::fromGlobals();

        if ('dev' !== App::getEnv()) {
            try {
                $response = self::dispatch($request);
            } catch (Throwable $e) {
                /** @noinspection ForgottenDebugOutputInspection */
                error_log((string) $e);
                $response = new Response\EmptyResponse(500);
            }
            (new SapiEmitter())->emit($response);
            return;
        }

        // In dev enviroment - no catch
        $response = self::dispatch($request);
        (new SapiEmitter())->emit($response);
    }

    public static function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $routeInfo = self::dispatcher()->dispatch($request->getMethod(), $request->getUri()->getPath());
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                [, $handler, $args] = $routeInfo;
                return $handler($request, $args);
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new Response\EmptyResponse(405);
            default:
                return new Response\EmptyResponse(404);
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

    public static function routes(RouteCollector $r): void
    {
        $r->addRoute(Action\Example::getMethods(), Action\Example::getPath(), [Action\Example::class, 'execute']);
    }
}
