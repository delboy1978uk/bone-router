<?php

namespace Bone\Router;

use Bone\Http\RouterInterface;
use League\Route\Route;
use League\Route\Router as LeagueRouter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router extends LeagueRouter implements RequestHandlerInterface, RouterInterface
{
    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param Route $route
     */
    public function removeRoute(Route $routeToRemove): void
    {
        foreach ($this->routes as $index => $route) {
            if ($route === $routeToRemove) {
                unset($this->routes[$index]);
            }
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatch($request);
    }
}
