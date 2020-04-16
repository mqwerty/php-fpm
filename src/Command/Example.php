<?php

/** @noinspection PhpMissingFieldTypeInspection */

namespace App\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Example extends Command
{
    protected static $defaultName = 'app:example';

    protected function configure(): void
    {
        $this
            ->setDescription('Пример команды')
            ->setAliases(['example']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Example');
        return 0;
    }
}
