<?php

declare(strict_types=1);

namespace Bone\Router;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Router\Command\RouterCommand;
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
        $consoleCommands[] = new RouterCommand($router);
        $c['consoleCommands'] = $consoleCommands;
    }
}
