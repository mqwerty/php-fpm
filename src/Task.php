<?php

namespace App;

class Task
{
    /**
     * @return string
     */
    public static function run(): string
    {
        return getenv('APP_ENV');
    }
}
