<?php

namespace App;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Throwable;

class Console
{
    protected CommandLoaderInterface $loader;
    protected LoggerInterface $logger;
    protected bool $consoleLogEx;

    public function __construct(CommandLoaderInterface $loader, LoggerInterface $logger, bool $consoleLogEx)
    {
        $this->loader = $loader;
        $this->logger = $logger;
        $this->consoleLogEx = $consoleLogEx;
    }

    public function handle(): void
    {
        $app = new Application();
        $app->setCommandLoader($this->loader);
        $app->setCatchExceptions(false);
        try {
            $app->run();
        } catch (Throwable $e) {
            if ($this->consoleLogEx) {
                $this->logger->error((string) $e);
            }
            echo $e . PHP_EOL;
        }
    }
}
