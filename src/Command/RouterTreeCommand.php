<?php

declare(strict_types=1);

namespace Bone\Router\Command;

use Bone\Http\RouterInterface;
use Bone\Router\Router;
use League\Route\Route;
use League\Route\RouteGroup;
use League\Route\Router as LeagueRouter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function array_keys;
use function array_shift;
use function count;
use function explode;
use function trim;
use function usort;

class RouterTreeCommand extends Command
{
    private bool $spacing = false;

    public function __construct(private readonly Router $router)
    {
        parent::__construct('router:tree');
    }

    public function configure(): void
    {
        $this->setDescription('Display all routes in a tree display');
        $this->setHelp('List all routes in a hierarchical tree');
        $this->addOption('space', 's', InputOption::VALUE_NONE,'display with more space');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->spacing = $input->getOption('space') ? true : false;
        $io = new SymfonyStyle($input, $output);
        $io->title('Configured routes :');
        $groups = $this->router->getGroups();

        foreach ($groups as $group) {
            $this->getRoutes($group);
        }

        $routes = $this->router->getRoutes();
        $sort = function (Route $a, Route $b) {
            return $a->getPath() <=> $b->getPath();
        };
        usort($routes, $sort);
        $paths = [];

        foreach ($routes as $route) {
            $paths[$route->getMethod()][] = $route->getPath();
        }

        foreach ($paths as $method => $methodPaths) {

            $io->info($method . ' paths:');
            $tree = $this->buildTree($methodPaths);
            $method === 'GET' ? array_shift($tree) : null;
            $output->writeln('/');
            $this->printTree($tree, $output, '');
            $io->writeln('');
        }

        return Command::SUCCESS;
    }

    private function buildTree(array $urls): array
    {
        $tree = [];
        foreach ($urls as $url) {
            $parts = explode('/', trim($url, '/')); // Split the URL into parts
            $current = &$tree; // Start at the root of the tree

            foreach ($parts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part]; // Move down the tree
            }
        }

        return $tree;
    }

    private function printTree(array $tree, OutputInterface $output, string $indent = '') : void
    {
        $keys = array_keys($tree);
        $totalKeys = count($keys);

        foreach ($keys as $index => $key) {
            if ($index === 0  && $this->spacing === true) {
                $output->writeln($indent . '|');
            }

            $output->write($indent . '|_ ' . $key);

            // Check if this node has only one child, in which case we collapse it
            $useChild = false;

            if (count($tree[$key]) == 1) {
                // Find the only child node and print it directly without further recursion
                $child = array_keys($tree[$key])[0];
                $output->write( '/' . $child);
                $useChild = true;
            }

            $output->write("\n");
            $node = $useChild ? $tree[$key][$child] : $tree[$key];

            // Recursively print the subtree, adjusting the indentation
            if (!empty($node) && count($node) > 1) {
                $newIndent = $index + 1 === $totalKeys ? $indent . '   ' : $indent . '|  ';
                $this->printTree($node, $output, $newIndent);
            }

            // If there's another sibling node, print '|'
            if ($index < $totalKeys - 1 && $this->spacing === true) {
                $output->write($indent . '| ' . "\n");  // Adjusts the visual output with proper spacing
            }
        }
    }

    private function getRoutes(RouteGroup $group): void
    {
        $mirror = new ReflectionClass(RouteGroup::class);
        $callback = $mirror->getProperty('callback')->getValue($group);
        $callback($group);
    }
}
