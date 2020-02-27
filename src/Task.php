<?php

namespace App;

class Task
{
    /**
     * @return string
     */
    public static function run(): string
    {
        return $_SERVER['HOSTNAME'] . PHP_EOL;
    }
}
