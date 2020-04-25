<?php

namespace App;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Goridge\StreamRelay;
use Spiral\RoadRunner\PSR7Client;
use Spiral\RoadRunner\Worker;
use Throwable;

use function FastRoute\cachedDispatcher;

final class Router
{
    private Dispatcher $dispatcher;

    public function __construct()
    {
        $this->dispatcher = cachedDispatcher(
            [self::class, 'routes'],
            [
                'cacheFile' => getenv('APP_ROUTE_CACHE') ?: '/tmp/app.route.cache',
                'cacheDisabled' => 'dev' === App::getEnv(),
            ]
        );
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
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

    public static function routes(RouteCollector $r): void
    {
        $r->addRoute(Action\Example::getMethods(), Action\Example::getPath(), [Action\Example::class, 'execute']);
    }

    public static function handle(): void
    {
        $relay = new StreamRelay(STDIN, STDOUT);
        $worker = new Worker($relay);
        $psr7 = new PSR7Client($worker);
        $router = new self();
        while ($request = $psr7->acceptRequest()) {
            try {
                $response = $router->dispatch($request);
                $psr7->respond($response);
            } catch (Throwable $e) {
                /** @noinspection ForgottenDebugOutputInspection */
                error_log((string) $e);
                $response = 'prod' === App::getEnv()
                    ? new Response\EmptyResponse(500)
                    : ErrorHandler::toResponce($e);
                $psr7->respond($response);
            }
        }
    }
}
