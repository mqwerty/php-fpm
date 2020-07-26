<?php

namespace App;

use App\Exception\HttpException;
use FastRoute\Dispatcher;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Router
{
    protected Dispatcher $dispatcher;
    protected ContainerInterface $container;
    protected LoggerInterface $logger;

    public function __construct(Dispatcher $dispatcher, ContainerInterface $container, LoggerInterface $logger)
    {
        $this->dispatcher = $dispatcher;
        $this->container = $container;
        $this->logger = $logger;
    }

    public function handle(): void
    {
        $request = ServerRequestFactory::fromGlobals();

        try {
            $response = $this->dispatch($request);
        } catch (Throwable $e) {
            $this->logger->error((string) $e);
            if ('dev' === $this->container->get('env')) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw $e;
            }
            $response = new Response\EmptyResponse(
                is_a($e, HttpException::class) ? $e->getCode() : 500
            );
        }

        (new SapiEmitter())->emit($response);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     */
    private function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                [, $handler, $args] = $routeInfo;
                $ctl = $this->container->get($handler[0]);
                $method = $handler[1];
                return $ctl->$method($request, $args);
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new Response\EmptyResponse(405);
            default:
                return new Response\EmptyResponse(404);
        }
    }
}
