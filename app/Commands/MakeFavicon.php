<?php
declare(strict_types=1);

namespace App\Commands;

use Imagick;
use ImagickException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeFavicon extends Command
{
    /** @var string */
    protected static $defaultName = 'make:favicon';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Make favicon from image')
            ->addArgument('source', InputArgument::REQUIRED, 'Image source')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $list = [
            512 => 'android-chrome-512x512.png',
            192 => 'android-chrome-192x192.png',
            180 => 'apple-touch-icon.png',
            48 => 'favicon.ico',
            32 => 'favicon-32x32.png',
            16 => 'favicon-16x16.png',
        ];

        try {
            foreach ($list as $size => $filename) {
                $im = new Imagick($input->getArgument('source'));
                $im->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1);
                $im->writeImage(dirname(__DIR__, 2) . '/public/' . $filename);
            }
        }
        catch (ImagickException $e) {
            $io = new SymfonyStyle($input, $output);
            $io->block($e->getMessage(), null, 'fg=white;bg=red', ' ', true);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
