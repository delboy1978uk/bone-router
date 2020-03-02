<?php

declare(strict_types=1);

namespace Bone\Router;

use Barnacle\Container;
use Barnacle\RegistrationInterface;

class RouterPackage implements RegistrationInterface
{
    /**
     * @param Container $c
     */
    public function addToContainer(Container $c)
    {
    }

    /**
     * @return string
     */
    public function getEntityPath(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    public function hasEntityPath(): bool
    {
        return false;
    }
}
