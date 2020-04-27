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
        $env = App::getEnv();
        $relay = new StreamRelay(STDIN, STDOUT);
        $worker = new Worker($relay);
        $psr7 = new PSR7Client($worker);
        $router = new self();
        while ($request = $psr7->acceptRequest()) {
            // jit debug, rr option http.workers.pool.maxJobs must be set to 1
            if ('prod' !== $env && array_key_exists('XDEBUG_SESSION', $request->getCookieParams())) {
                /** @noinspection ForgottenDebugOutputInspection PhpComposerExtensionStubsInspection */
                xdebug_break();
            }
            try {
                $response = $router->dispatch($request);
                $psr7->respond($response);
            } catch (Throwable $e) {
                /** @noinspection ForgottenDebugOutputInspection */
                error_log((string) $e);
                $response = 'prod' === $env
                    ? new Response\EmptyResponse(500)
                    : self::exToResponce($e);
                $psr7->respond($response);
            }
        }
    }

    public static function exToArray(Throwable $e): array
    {
        return [
            'status' => 'error',
            'error' => [
                'class' => get_class($e),
                'msg' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ],
        ];
    }

    public static function exToResponce($e): Response
    {
        return new Response\JsonResponse(
            static::exToArray($e),
            500,
            [],
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }
}
