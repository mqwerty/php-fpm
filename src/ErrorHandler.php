<?php

namespace App;

use Throwable;
use Laminas\Diactoros\Response;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run as ErrorRunner;

class ErrorHandler
{
    public static function register(): void
    {
        (new ErrorRunner())
            ->pushHandler(
                new PlainTextHandler()
            )
            ->register();
    }

    public static function toArray(Throwable $e): array
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

    public static function toResponce($e): Response
    {
        return new Response\JsonResponse(
            static::toArray($e),
            500,
            [],
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }
}
