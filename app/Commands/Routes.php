<?php
declare(strict_types=1);

namespace App\Commands;

use App\Core;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Routes extends Command
{
    /** @var string */
    protected static $defaultName = 'routes';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('List all registered routes')
            ->addOption('method', null, InputOption::VALUE_REQUIRED,
                'Filter by method (GET, DELETE, OPTIONS, PATCH, POST or PUT)')
            ->addOption('uri', null, InputOption::VALUE_REQUIRED, 'Filter by uri')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Filter by name')
            ->addOption('sort', 's', InputOption::VALUE_REQUIRED, 'Sort by column (method, uri, or name)', 'uri')
            ->addOption('reverse', 'r', InputOption::VALUE_NONE, 'Reverse the sort order');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $slim = Core::create(dirname(__DIR__, 2));
        $methodFilter = $input->getOption('method');
        $uriFilter = $input->getOption('uri');
        $nameFilter = $input->getOption('name');
        $routeList = [];

        foreach ($slim->getRouteCollector()->getRoutes() as $route) {
            $routeMethods = $route->getMethods();
            $routePattern = $route->getPattern();
            $routeName = $route->getName() ?? '';
            $routeCallable = $route->getCallable();

            if ($this->matchesMethod($methodFilter, $routeMethods) && $this->matchesUri($uriFilter, $routePattern) &&
                $this->matchesName($nameFilter, $routeName)) {
                $routeList[] = (object)[
                    'Method' => implode('|', $routeMethods),
                    'URI' => $routePattern,
                    'Name' => $routeName,
                    'Callable' => $this->getCallable($routeCallable),
                ];
            }
        }

        $sortOptions = preg_grep('`^' . $input->getOption('sort') . '$`i', ['Method', 'URI', 'Name']);
        if (!$sortOptions) {
            $io = new SymfonyStyle($input, $output);
            $io->block('--sort option is one of "Method", "URI" or "Name"', null, 'fg=white;bg=red', ' ', true);
            return Command::FAILURE;
        }
        $sortMethod = array_shift($sortOptions);

        if (count($routeList)) {
            usort($routeList, static function ($a, $b) use ($sortMethod) {
                return strcmp($a->{$sortMethod}, $b->{$sortMethod});
            });

            if ($input->getOption('reverse')) {
                $routeList = array_reverse($routeList);
            }

            $this->table($output, $routeList);
        }
        else {
            $output->writeln('<comment>No routes defined</comment>');
        }

        return Command::SUCCESS;
    }

    /**
     * @param string|null $expr
     * @param array $methods
     * @return bool
     */
    protected function matchesMethod(?string $expr, array $methods): bool
    {
        if (is_null($expr)) {
            return true;
        }

        return in_array(strtoupper($expr), array_map('strtoupper', $methods), true);
    }

    /**
     * @param string|null $expr
     * @param string $uri
     * @return bool
     */
    protected function matchesUri(?string $expr, string $uri): bool
    {
        if (is_null($expr)) {
            return true;
        }

        return str_contains(strtolower($uri), strtolower($expr));
    }

    /**
     * @param string|null $expr
     * @param string $name
     * @return bool
     */
    protected function matchesName(?string $expr, string $name): bool
    {
        if (is_null($expr)) {
            return true;
        }
        if ($name === '') {
            return false;
        }

        return str_contains(strtolower($name), strtolower($expr));
    }

    /**
     * @param OutputInterface $output
     * @param array $rows
     */
    protected function table(OutputInterface $output, array $rows): void
    {
        $header = '+-';
        $format = '| ';

        foreach ($rows[0] as $key => $value) {
            $length = max(strlen($key), max(array_map('strlen', array_column($rows, $key))));
            $header .= str_repeat('-', $length) . '-+-';
            $format .= '%-' . $length . 's | ';
        }

        $output->writeln(rtrim($header, '-'));
        $output->writeln(rtrim(vsprintf($format, array_keys((array)$rows[0]))));
        $output->writeln(rtrim($header, '-'));

        foreach ($rows as $row) {
            $output->writeln(rtrim(vsprintf($format, array_values((array)$row))));
        }

        $output->writeln(rtrim($header, '-'));
    }

    /**
     * @param $callable
     * @return string
     */
    private function getCallable($callable): string
    {
        if (is_string($callable)) {
            return $callable;
        }

        if (is_array($callable) && (count($callable) === 2)) {
            return $callable[0] . '::' . $callable[1];
        }

        return '(Closure)';
    }
}
