<?php
declare(strict_types=1);

namespace App\Commands;

use App\Classes\Database;
use App\Core;
use PDOException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeSuperuser extends Command
{
    /** @var string */
    protected static $defaultName = 'make:superuser';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('List all registered routes')
            ->addOption('username', null, InputOption::VALUE_OPTIONAL, 'Username')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Email');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getOption('username');
        if (!$username) {
            $username = $this->getHelper('question')->ask($input, $output, new Question('Username: '));
        }

        $email = $input->getOption('email');
        if (!$email) {
            $email = $this->getHelper('question')->ask($input, $output, new Question('Email Address: '));
        }

        $password = $this->getHelper('question')->ask($input, $output,
            (new Question('Password: '))->setHidden(true)->setHiddenFallback(false)
        );

        $rules = v::arrayVal()
            ->key('username', v::alnum('_@+-.')->length(1, 50))
            ->key('email', v::email())
            ->key('password', v::length(4));

        try {
            $rules->assert(['username' => $username, 'email' => $email, 'password' => $password]);
        } catch (NestedValidationException $e) {
            $errors = $e->getMessages();
            $io = new SymfonyStyle($input, $output);
            $io->block(implode("\n", $errors), null, 'fg=white;bg=red', ' ', true);
            return Command::FAILURE;
        }

        $slim = Core::create(dirname(__DIR__, 2));
        $container = $slim->getContainer();
        if (!$container) {
            $io = new SymfonyStyle($input, $output);
            $io->block('Application container is not available', null, 'fg=white;bg=red', ' ', true);
            return Command::FAILURE;
        }

        /** @var Database $db */
        $db = $container->get(Database::class);
        $sql = <<<SQL
            INSERT INTO `users` (`username`, `email`, `password`, `staff`, `superuser`)
            VALUES (:username, :email, :password, 1, 1)
SQL;
        try {
            $db->execute($sql, [
                'username' => strtolower($username),
                'email' => strtolower($email),
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $e) {
            $io = new SymfonyStyle($input, $output);
            $io->block($e->getMessage(), null, 'fg=white;bg=red', ' ', true);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
