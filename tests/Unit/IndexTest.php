<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Unit;

use App\Controller\IndexController;
use Codeception\Test\Unit;
use Laminas\Diactoros\ServerRequest;
use Monolog\Logger;
use UnitTester;

class IndexTest extends Unit
{
    protected UnitTester $tester;

    public function testIndex(): void
    {
        $ctl = new IndexController(new Logger('test'));
        $result = $ctl->index(new ServerRequest());
        static::assertSame(200, $result->getStatusCode());
        static::assertSame('Example', $result->getBody()->__toString());
    }
}
