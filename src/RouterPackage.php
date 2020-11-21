<?php

declare(strict_types=1);

namespace Bone\Router;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Router\Router;
use Bone\Router\Decorator\ExceptionDecorator;
use Bone\Router\Decorator\NotAllowedDecorator;
use Bone\Router\Decorator\NotFoundDecorator;
use Bone\Router\PlatesStrategy;
use Bone\View\ViewEngine;
use League\Route\Strategy\ApplicationStrategy;

class RouterPackage implements RegistrationInterface
{
    /**
     * @param Container $c
     */
    public function addToContainer(Container $c)
    {
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($c);
        $router = $c[Router::class] = new Router();
        $router->setStrategy($strategy);
    }
}
