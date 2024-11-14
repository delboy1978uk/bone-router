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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function usort;

class RouterCommand extends Command
{

    public function __construct(
        private readonly Router $router,
        private readonly array $blockedRoutes,
        private readonly array $routeMiddleware
    ) {
        parent::__construct('router:list');
    }

    public function configure(): void
    {
        $this->setDescription('Lists all routes registered with the router');
        $this->setHelp('List all routes');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
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
            $path = $route->getPath();
            $method = $route->getMethod();
            $blocked = $this->isBlocked($method, $path);
            $middlewared = $this->isMiddlewared($method, $path);

            if ($blocked) {
               $path = '<fg=red>' .$path . '</>';
            }

            if ($middlewared) {
               $path = '<fg=magenta>' .$path . '</>';
            }

            $paths[] = [$method, $path];
        }

        $io->table(['Method', 'Path'], $paths);

        return Command::SUCCESS;
    }

    private function isMiddlewared(string $method, string $path): bool
    {
        return in_array($path, $this->routeMiddleware)
            || (isset($this->routeMiddleware[$method]) && array_key_exists($path, $this->routeMiddleware[$method]));
    }

    private function isBlocked(string $method, string $path): bool
    {
        return in_array($path, $this->blockedRoutes)
            || (isset($this->blockedRoutes[$method]) && in_array($path, $this->blockedRoutes[$method]));
    }

    private function getRoutes(RouteGroup $group)
    {
        $mirror = new ReflectionClass(RouteGroup::class);
        $callback = $mirror->getProperty('callback')->getValue($group);
        $callback($group);
    }
}
