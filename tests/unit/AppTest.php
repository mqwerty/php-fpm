<?php

use App\App;
use Codeception\Test\Unit;
use Whoops\Run as ErrorHandler;

class AppTest extends Unit
{
    protected UnitTester $tester;

    public function testLoadDotEnv(): void
    {
        putenv('APP_ENV');
        $method = $this->tester::getReflectionMethod(App::class, 'loadDotEnv');
        $method->invokeArgs(null, []);
        $this->assertFalse(getenv('APP_ENV'));
    }

    public function testIniSet(): void
    {
        $method = $this->tester::getReflectionMethod(App::class, 'setIni');

        putenv('APP_ENV=prod');
        $method->invokeArgs(null, []);
        $this->assertContains(ini_get('display_errors'), ['', 0, '0', 'Off', 'off']);

        putenv('APP_ENV=dev');
        $method->invokeArgs(null, []);
        $this->assertContains(ini_get('display_errors'), [1, '1', 'On', 'on']);
    }

    public function testSetErrorHandler(): void
    {
        $method = $this->tester::getReflectionMethod(App::class, 'setErrorHandler');
        $method->invokeArgs(null, []);
        $prev_handler = set_error_handler(fn($code, $message) => false);
        $this->assertInstanceOf(ErrorHandler::class, $prev_handler[0]);
        restore_error_handler();
        restore_error_handler();
    }

    public function testHello(): void
    {
        $result = App::hello();
        $this->assertEquals($result, 'Hello, World!');
    }
}
