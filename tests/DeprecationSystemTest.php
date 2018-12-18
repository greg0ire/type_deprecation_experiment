<?php

namespace Greg0ire\TypeDeprecationExperiment\Tests;

use Greg0ire\TypeDeprecationExperiment\Foo;
use Greg0ire\TypeDeprecationExperiment\LegacyFoo;
use PHPUnit\Framework\TestCase;

final class DeprecationSystemTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testBothClassesAreInFactTheSameType()
    {
        self::assertInstanceOf(LegacyFoo::class, new Foo());
        self::assertInstanceOf(Foo::class, new LegacyFoo());
    }

    /**
     * @group legacy
     */
    public function testAClassConsumingTheNewTypeCanReceiveTheOldType()
    {
        new class (new LegacyFoo) {
            public function __construct(Foo $foo)
            {
            }
        };
        self::assertTrue(true); // Look, Ma! No crash!
    }

    /**
     * @group legacy
     */
    public function testAClassConsumingTheOldTypeCanReceiveTheNewType()
    {
        new class (new Foo) {
            public function __construct(LegacyFoo $foo)
            {
            }
        };
        self::assertTrue(true); // Look, Ma! No crash!
    }

    /**
     * @group legacy
     * @expectedDeprecation LegacyFoo is deprecated!
     */
    public function testADeprecationIsTriggeredWhenLoadingLegacyFoo()
    {
        $this->assertInstanceOf(LegacyFoo::class, new LegacyFoo);
    }

    public function testNoDeprecationIsTriggeredWhenLoadingFoo()
    {
        $this->assertInstanceOf(Foo::class, new Foo);
    }
}
