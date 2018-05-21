<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap\Test\Unit;

use PHPUnit\Framework\TestCase;
use Stratadox\IdentityMap\IdentityMap;
use Stratadox\IdentityMap\NoSuchObject;
use Stratadox\IdentityMap\Test\Unit\Fixture\Bar;
use Stratadox\IdentityMap\Test\Unit\Fixture\Baz;
use Stratadox\IdentityMap\Test\Unit\Fixture\Foo;
use Stratadox\IdentityMap\Whitelist;

/**
 * @covers \Stratadox\IdentityMap\Whitelist
 */
class Whitelist_entity_classes extends TestCase
{
    /** @test */
    function keeping_only_the_allowed_classes_from_the_original_map()
    {
        $foo = new Foo;
        $bar = new Bar;

        $map = Whitelist::forThe(IdentityMap::with([
            'foo' => $foo,
            'bar' => $bar,
        ]), Bar::class);

        $this->assertFalse($map->has(Foo::class, 'foo'));
        $this->assertFalse($map->hasThe($foo));
        $this->assertTrue($map->has(Bar::class, 'bar'));
        $this->assertTrue($map->hasThe($bar));
    }

    /** @test */
    function silently_ignoring_add_operations_for_non_whitelisted_classes()
    {
        $foo = new Foo;

        $map = Whitelist::the(Bar::class)->add('foo', $foo);

        $this->assertFalse($map->has(Foo::class, 'foo'));
        $this->assertFalse($map->hasThe($foo));
    }

    /** @test */
    function having_whitelisted_entities_in_the_map()
    {
        $bar = new Bar;

        $map = Whitelist::the(Bar::class)->add('bar', $bar);

        $this->assertTrue($map->has(Bar::class, 'bar'));
        $this->assertTrue($map->hasThe($bar));
    }

    /** @test */
    function ignoring_non_whitelisted_instances_after_allowing_an_object()
    {
        $bar = new Bar;
        $foo = new Foo;

        $map = Whitelist::the(Bar::class)
            ->add('bar', $bar)
            ->add('foo', $foo);

        $this->assertFalse($map->has(Foo::class, 'foo'));
        $this->assertFalse($map->hasThe($foo));
    }

    /** @test */
    function getting_whitelisted_entities_from_the_map()
    {
        $bar = new Bar;

        $map = Whitelist::the(Bar::class)->add('bar', $bar);

        $this->assertSame($bar, $map->get(Bar::class, 'bar'));
    }

    /** @test */
    function getting_the_id_of_whitelisted_instances_from_the_map()
    {
        $bar = new Bar;

        $map = Whitelist::the(Bar::class)->add('bar', $bar);

        $this->assertSame('bar', $map->idOf($bar));
    }

    /** @test */
    function trying_to_get_the_id_of_non_whitelisted_instances_from_the_map()
    {
        $foo = new Foo;

        $map = Whitelist::the(Bar::class)->add('foo', $foo);

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object of class `' . Foo::class . '` is not in the identity map.'
        );

        $map->idOf($foo);
    }

    /** @test */
    function removing_whitelisted_entities_from_the_map()
    {
        $map = Whitelist::forThe(IdentityMap::with([
            'bar' => new Bar,
        ]), Bar::class)->remove(Bar::class, 'bar');

        $this->assertFalse($map->has(Bar::class, 'bar'));
    }

    /** @test */
    function trying_to_remove_a_non_whitelisted_class()
    {
        $map = Whitelist::the(Bar::class)
            ->add('foo', new Foo);

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object with id `foo` of class `' . Foo::class . '` ' .
            'is not in the identity map.'
        );

        $map->remove(Foo::class, 'foo');
    }

    /** @test */
    function ignoring_non_whitelisted_instances_after_removing_an_object()
    {
        $bar = new Bar;
        $foo = new Foo;

        $map = Whitelist::the(Bar::class)
            ->add('bar', $bar)
            ->remove(Bar::class, 'bar')
            ->add('foo', $foo);

        $this->assertFalse($map->has(Foo::class, 'foo'));
        $this->assertFalse($map->hasThe($foo));
    }

    /** @test */
    function removing_whitelisted_instances_from_the_map()
    {
        $bar = new Bar;

        $map = Whitelist::forThe(IdentityMap::with([
            'bar' => $bar,
        ]), Bar::class)->removeThe($bar);

        $this->assertFalse($map->hasThe($bar));
        $this->assertFalse($map->has(Bar::class, 'bar'));
    }

    /** @test */
    function trying_to_remove_a_non_whitelisted_instance()
    {
        $foo = new Foo;

        $map = Whitelist::the(Bar::class)
            ->add('foo', $foo);

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object of class `' . Foo::class . '` is not in the identity map.'
        );

        $map->removeThe($foo);
    }

    /** @test */
    function ignoring_non_whitelisted_instances_after_removing_an_instance()
    {
        $bar = new Bar;
        $foo = new Foo;

        $map = Whitelist::the(Bar::class)
            ->add('bar', $bar)
            ->removeThe($bar)
            ->add('foo', $foo);

        $this->assertFalse($map->has(Foo::class, 'foo'));
        $this->assertFalse($map->hasThe($foo));
    }

    /** @test */
    function removing_all_objects_of_whitelisted_classes()
    {
        $bar = new Bar;

        $map = Whitelist::the(Bar::class)
            ->add('bar', $bar)
            ->removeAllObjectsOfThe(Bar::class);

        $this->assertFalse($map->hasThe($bar));
        $this->assertFalse($map->has(Bar::class, 'bar'));
    }

    /** @test */
    function removing_all_objects_of_non_whitelisted_classes_has_no_effect()
    {
        $map = Whitelist::the(Bar::class)
            ->add('foo', new Foo)
            ->add('bar', new Bar);

        $this->assertSame($map, $map->removeAllObjectsOfThe(Foo::class));
    }

    /** @test */
    function ignoring_non_whitelisted_instances_after_purging_a_class()
    {
        $bar = new Bar;
        $foo = new Foo;

        $map = Whitelist::the(Bar::class)
            ->add('bar', $bar)
            ->removeAllObjectsOfThe(Bar::class)
            ->add('foo', $foo);

        $this->assertFalse($map->has(Foo::class, 'foo'));
        $this->assertFalse($map->hasThe($foo));
    }

    /** @test */
    function listing_all_registered_classes()
    {

        $map = Whitelist::forThe(IdentityMap::with([
            new Foo,
            new Bar,
            new Baz,
        ]), Foo::class, Bar::class, Whitelist::class);

        $this->assertSame([
            Foo::class,
            Bar::class
        ], $map->classes());
    }
}
