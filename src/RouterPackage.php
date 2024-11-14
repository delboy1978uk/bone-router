<?php

declare(strict_types=1);

namespace Bone\Router;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Router\Command\RouterCommand;
use Bone\Router\Command\RouterTreeCommand;
use Bone\Router\Router;
use League\Route\Strategy\ApplicationStrategy;

class RouterPackage implements RegistrationInterface
{
    public function addToContainer(Container $c): void
    {
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($c);
        $router = $c[Router::class] = new Router();
        $router->setStrategy($strategy);
        $consoleCommands = $c->has('consoleCommands') ? $c->get('consoleCommands') : [];
        $blockedRoutes = $c->has('blockedRoutes') ? $c->get('blockedRoutes') : [];
        $routeMiddleware = $c->has('routeMiddleware') ? $c->get('routeMiddleware') : [];
        $consoleCommands[] = new RouterCommand($router, $blockedRoutes, $routeMiddleware);
        $consoleCommands[] = new RouterTreeCommand($router);
        $c['consoleCommands'] = $consoleCommands;
    }
}
