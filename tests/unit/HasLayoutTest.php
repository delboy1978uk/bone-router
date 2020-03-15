<?php declare(strict_types=1);

namespace BoneTest;

use Bone\Router\Traits\HasLayoutTrait;
use Codeception\TestCase\Test;

class HasLayoutTest extends Test
{
    public function testLayout()
    {
        $class = new class {
          use HasLayoutTrait;
        };

        $class->setLayout('xxx');
        $this->assertEquals('xxx', $class->getLayout());
    }
}
