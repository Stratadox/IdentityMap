<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap\Test\Unit;

use PHPUnit\Framework\TestCase;
use Stratadox\IdentityMap\IdentityMap;
use Stratadox\IdentityMap\Ignore;
use Stratadox\IdentityMap\NoSuchObject;
use Stratadox\IdentityMap\Test\Unit\Fixture\Bar;
use Stratadox\IdentityMap\Test\Unit\Fixture\Foo;

/**
 * @covers \Stratadox\IdentityMap\Ignore
 */
class Ignore_all_instances_of_a_class extends TestCase
{
    /** @test */
    function silently_ignoring_add_operations_on_instances_of_the_class()
    {
        $map = Ignore::the(Foo::class, IdentityMap::startEmpty());

        $foo = new Foo;
        $map = $map->add('foo', $foo);

        $this->assertFalse($map->has(Foo::class, 'foo'));
        $this->assertFalse($map->hasThe($foo));
    }

    /** @test */
    function having_instances_in_the_map_that_are_not_ignored()
    {
        $map = Ignore::the(Foo::class, IdentityMap::startEmpty());

        $bar = new Bar;
        $map = $map->add('bar', $bar);

        $this->assertTrue($map->has(Bar::class, 'bar'));
        $this->assertTrue($map->hasThe($bar));
    }

    /** @test */
    function filtering_ignored_objects_from_the_original_map()
    {
        $foo = new Foo;
        $bar = new Bar;

        $map = Ignore::the(Foo::class, IdentityMap::with([
            'foo' => $foo,
            'bar' => $bar,
        ]));

        $this->assertFalse($map->has(Foo::class, 'foo'));
        $this->assertFalse($map->hasThe($foo));
        $this->assertTrue($map->has(Bar::class, 'bar'));
        $this->assertTrue($map->hasThe($bar));
    }

    /** @test */
    function getting_a_non_ignored_object_from_the_map()
    {
        $map = Ignore::the(Foo::class, IdentityMap::startEmpty());

        $bar = new Bar;
        $map = $map->add('bar', $bar);

        $this->assertSame($bar, $map->get(Bar::class, 'bar'));
    }

    /** @test */
    function removing_non_ignored_objects()
    {
        $map = Ignore::the(Foo::class, IdentityMap::with(['bar' => new Bar]));

        $map = $map->remove(Bar::class, 'bar');

        $this->assertFalse($map->has(Bar::class, 'bar'));
    }

    /** @test */
    function silently_ignoring_remove_operations_on_ignored_objects()
    {
        $map = Ignore::the(Foo::class, IdentityMap::with(['foo' => new Foo]));

        $remove = $map->remove(Foo::class, 'foo');

        $this->assertSame($map, $remove);
    }

    /** @test */
    function removing_non_ignored_classes()
    {
        $map = Ignore::the(Foo::class, IdentityMap::with(['bar' => new Bar]));

        $map = $map->removeAllObjectsOfThe(Bar::class);

        $this->assertFalse($map->has(Bar::class, 'bar'));
    }

    /** @test */
    function removing_non_ignored_instances_from_the_map()
    {
        $foo1 = new Foo;
        $foo2 = new Foo;
        $map = Ignore::the(Bar::class, IdentityMap::with([
            'foo1' => $foo1,
            'foo2' => $foo2,
        ]));

        $map = $map->removeThe($foo1);

        $this->assertFalse($map->has(Foo::class, 'foo1'));
        $this->assertFalse($map->hasThe($foo1));
        $this->assertTrue($map->has(Foo::class, 'foo2'));
        $this->assertTrue($map->hasThe($foo2));
    }

    /** @test */
    function silently_ignoring_remove_operations_on_ignored_classes()
    {
        $map = Ignore::the(Foo::class, IdentityMap::with(['foo' => new Foo]));

        $remove = $map->removeAllObjectsOfThe(Foo::class);

        $this->assertSame($map, $remove);
    }

    /** @test */
    function getting_the_id_of_a_non_ignored_object_from_the_map()
    {
        $map = Ignore::the(Foo::class, IdentityMap::startEmpty());

        $bar = new Bar;
        $map = $map->add('bar', $bar);

        $this->assertSame('bar', $map->idOf($bar));
    }

    /** @test */
    function throwing_an_exception_when_removing_an_unregistered_instance()
    {
        $map = Ignore::the(Bar::class, IdentityMap::startEmpty());

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object of class `' . Foo::class . '` is not in the identity map.'
        );

        $map->removeThe(new Foo);
    }

    /** @test */
    function throwing_an_exception_when_removing_an_ignored_instance()
    {
        $foo = new Foo;
        $map = Ignore::the(Foo::class, IdentityMap::with([
            'foo' => $foo
        ]));

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object of class `' . Foo::class . '` is not in the identity map.'
        );

        $map->removeThe($foo);
    }
}
