<?php

namespace App\Action;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Example extends Action
{
    protected static array $methods = ['GET'];
    protected static string $path = '/';

    public static function execute(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('Example');
        return $response;
    }
}
