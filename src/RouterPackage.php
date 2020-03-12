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

class RouterPackage implements RegistrationInterface
{
    /**
     * @param Container $c
     */
    public function addToContainer(Container $c)
    {
        $c[ViewEngine::class] = $c->get(ViewEngine::class);

        $c[NotFoundDecorator::class] = $c->factory(function (Container $c) {
            $layout = $c->get('default_layout');
            $templates = $c->get('error_pages');
            $viewEngine = $c->get(ViewEngine::class);
            $notFoundDecorator = new NotFoundDecorator($viewEngine, $templates);
            $notFoundDecorator->setLayout($layout);

            return $notFoundDecorator;
        });

        $c[NotAllowedDecorator::class] = $c->factory(function (Container $c) {
            $layout = $c->get('default_layout');
            $templates = $c->get('error_pages');
            $viewEngine = $c->get(ViewEngine::class);
            $notAllowedDecorator = new NotAllowedDecorator($viewEngine, $templates);
            $notAllowedDecorator->setLayout($layout);

            return $notAllowedDecorator;
        });

        $c[ExceptionDecorator::class] = $c->factory(function (Container $c) {
            $viewEngine = $c->get(ViewEngine::class);
            $layout = $c->get('default_layout');
            $templates = $c->get('error_pages');
            $decorator = new ExceptionDecorator($viewEngine, $templates);
            $decorator->setLayout($layout);

            return $decorator;
        });

        $c[PlatesStrategy::class] = $c->factory(function (Container $c) {
            $viewEngine = $c->get(ViewEngine::class);
            $notFoundDecorator = $c->get(NotFoundDecorator::class);
            $notAllowedDecorator = $c->get(NotAllowedDecorator::class);
            $exceptionDecorator = $c->get(ExceptionDecorator::class);
            $layout = $c->get('default_layout');
            $strategy = new PlatesStrategy($viewEngine, $notFoundDecorator, $notAllowedDecorator, $layout, $exceptionDecorator);

            return $strategy;
        });

        /** @var PlatesStrategy $strategy */
        $strategy = $c->get(PlatesStrategy::class);
        $strategy->setContainer($c);

        $router = $c->get(Router::class);
        $router->setStrategy($strategy);
    }
}
