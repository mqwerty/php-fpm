<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Action
{
    /**
     * @var string[]
     */
    protected static array $methods = [];
    protected static string $path = '';

    abstract public static function execute(ServerRequestInterface $request, array $args = []): ResponseInterface;

    public static function getPath(): string
    {
        return static::$path;
    }

    public static function getMethods(): array
    {
        return static::$methods;
    }
}
