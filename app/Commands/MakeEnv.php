<?php
declare(strict_types=1);

namespace App\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeEnv extends Command
{
    /** @var string */
    protected static $defaultName = 'make:env';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Create a basic .env file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = dirname(__DIR__, 2);
        $file = $path . '/.env';
        if (file_exists($file)) {
            $io = new SymfonyStyle($input, $output);
            $io->block('.env alread exists, cowardly refusing to overwrite it', null, 'fg=white;bg=red', ' ', true);
            return Command::FAILURE;
        }

        $contents = str_replace(['<key>', ], [base64_encode(random_bytes(32)), ],
            file_get_contents($path . '/.env.example'));
        file_put_contents($file, $contents);

        return Command::SUCCESS;
    }
}
