<?php

namespace App;

use App\Exception\HttpException;
use FastRoute\Dispatcher;
use Laminas\Diactoros\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spiral\Goridge\StreamRelay;
use Spiral\RoadRunner\PSR7Client;
use Spiral\RoadRunner\Worker;
use Throwable;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class Router
{
    protected Dispatcher $dispatcher;
    protected ContainerInterface $container;
    protected LoggerInterface $logger;
    protected PSR7Client $client;
    protected string $env;

    public function __construct(Dispatcher $dispatcher, ContainerInterface $container, LoggerInterface $logger)
    {
        $this->dispatcher = $dispatcher;
        $this->container = $container;
        $this->logger = $logger;
        $this->client = new PSR7Client(new Worker(new StreamRelay(STDIN, STDOUT)));
        $this->env = $this->container->get('env');
    }

    public function handle(): void
    {
        try {
            while ($request = $this->client->acceptRequest()) {
                // jit debug, no autostart, see xdebug.ini
                if (isset($xdebugSession)) {
                    $this->client->getWorker()->stop();
                    return;
                }
                if (
                    'prod' !== $this->env
                    && (
                        array_key_exists('XDEBUG_SESSION', $request->getCookieParams())
                        || array_key_exists('XDEBUG_SESSION', $request->getAttributes())
                        || array_key_exists('XDEBUG_SESSION', $request->getQueryParams())
                        || array_key_exists('Xdebug_session', $request->getHeaders())
                    )
                ) {
                    /** @noinspection ForgottenDebugOutputInspection */
                    xdebug_break();
                    $xdebugSession = true;
                }
                // handle request
                $this->handleRequest($this->client, $request);
            }
        } catch (Throwable $e) {
            $this->client->getWorker()->error((string) $e);
            return;
        }
    }

    protected function handleRequest(PSR7Client $psr7, ServerRequestInterface $request): void
    {
        try {
            $response = $this->dispatch($request);
        } catch (HttpException $e) {
            $this->logger->error((string) $e);
            $response = 'prod' === $this->env
                ? new Response\JsonResponse(['error' => $e->getMessage()], $e->getCode())
                : static::exToResponce($e);
        } catch (Throwable $e) {
            $this->logger->error((string) $e);
            $response = 'prod' === $this->env
                ? new Response\EmptyResponse(500)
                : static::exToResponce($e);
        }
        $psr7->respond($response);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     */
    protected function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                [, $handler, $args] = $routeInfo;
                $ctl = $this->container->get($handler[0]);
                $method = $handler[1];
                return $ctl->$method($this->parse($request), $args);
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new Response\EmptyResponse(405);
            default:
                return new Response\EmptyResponse(404);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws HttpException
     */
    protected function parse(ServerRequestInterface $request): ServerRequestInterface
    {
        if ($request->getBody()->getSize()) {
            try {
                $json = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
                $request = $request->withParsedBody($json);
            } catch (Throwable $e) {
                throw new HttpException(400, 'Parcing body error', $e);
            }
        }
        return $request;
    }

    protected static function exToArray(Throwable $e): array
    {
        return [
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

    protected static function exToResponce(Throwable $e): Response
    {
        return new Response\JsonResponse(
            static::exToArray($e),
            is_a($e, HttpException::class) ? $e->getCode() : 500,
            [],
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }
}
