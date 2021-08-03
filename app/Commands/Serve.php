<?php
declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Serve extends Command
{
    /** @var string */
    protected static $defaultName = 'serve';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Serve the application with PHP\'s built-in webserver')
            ->addOption('address', null, InputOption::VALUE_REQUIRED, 'Listen on address', '0.0.0.0')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Listen on port', 8000);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $address = $input->getOption('address');
        $port = $input->getOption('port');
        $process = new Process(['php', '-S', $address . ':' . $port, '-t', 'public', './serve.php']);

        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        $process->run(function (string $type, string $buffer) {
            echo $buffer;
        });

        return Command::SUCCESS;
    }
}
