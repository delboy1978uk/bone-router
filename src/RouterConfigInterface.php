<?php

namespace Bone\Router;

use Barnacle\Container;

interface RouterConfigInterface
{
    public function addRoutes(Container $c, Router $router);
}
