<?php

namespace App\Controller;

use FastRoute\RouteCollector;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class IndexController implements ControllerBase
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function routes(RouteCollector $r): void
    {
        $r->get('/', [self::class, 'index']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @return ResponseInterface
     * @noinspection PhpUnusedParameterInspection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $this->logger->debug('Example');
        return new Response\JsonResponse(['result' => 'test']);
    }
}
