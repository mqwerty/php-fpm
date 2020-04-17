<?php

namespace App;

use App\Console\Console;

final class App
{
    /**
     * App constructor.
     *
     * @phan-suppress PhanNoopNew
     */
    public function __construct()
    {
        new ErrorHandler();
        if ('cli' === PHP_SAPI) {
            new Console();
        }
    }
}
