<?php

namespace Bone\Router;

use Bone\Contracts\Container\ContainerInterface;
use Bone\Http\Middleware\JsonParse;
use Bone\Http\RouterInterface;
use Laminas\Diactoros\ResponseFactory;
use League\Route\Route;
use League\Route\RouteGroup;
use League\Route\Router as LeagueRouter;
use League\Route\Strategy\JsonStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router extends LeagueRouter implements RequestHandlerInterface, RouterInterface
{
    /** @return Route[] */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /** @return RouteGroup[] */
    public function getGroups(): array
    {
        return $this->groups;
    }

    public function apiResource(string $urlSlug, string $controllerClass, ContainerInterface $c): RouteGroup
    {
        $factory = new ResponseFactory();
        $strategy = new JsonStrategy($factory);
        $strategy->setContainer($c);
        $group = $this->group('/api', function (RouteGroup $route) use ($controllerClass, $urlSlug) {
            $route->map('GET', '/' . $urlSlug, [$controllerClass, 'index']);
            $route->map('POST', '/' . $urlSlug, [$controllerClass, 'create']);
            $route->map('GET', '/' . $urlSlug . '/{id}', [$controllerClass, 'read']);
            $route->map('PATCH', '/' . $urlSlug . '/{id}', [$controllerClass, 'update']);
            $route->map('DELETE', '/' . $urlSlug . '/{id}', [$controllerClass, 'delete']);
        });
        $group->setStrategy($strategy);
        $group->middlewares([new JsonParse()]);

        return $group;
    }

    public function removeRoute(Route $routeToRemove): void
    {
        foreach ($this->routes as $index => $route) {
            if ($route === $routeToRemove) {
                unset($this->routes[$index]);
            }
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatch($request);
    }
}
