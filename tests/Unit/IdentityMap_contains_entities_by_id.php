<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap\Test\Unit;

use PHPUnit\Framework\TestCase;
use Stratadox\IdentityMap\AlreadyThere;
use Stratadox\IdentityMap\IdentityMap;
use Stratadox\IdentityMap\NoSuchObject;
use Stratadox\IdentityMap\Test\Unit\Fixture\Bar;
use Stratadox\IdentityMap\Test\Unit\Fixture\Foo;

/**
 * @covers \Stratadox\IdentityMap\IdentityMap
 * @covers \Stratadox\IdentityMap\IdentityNotFound
 * @covers \Stratadox\IdentityMap\DuplicationDetected
 */
class IdentityMap_contains_entities_by_id extends TestCase
{
    /** @test */
    function having_the_object_in_the_map()
    {
        $this->assertTrue(IdentityMap::with([
            'foo' => new Foo
        ])->has(Foo::class, 'foo'));
    }

    /** @test */
    function using_a_numeric_identity()
    {
        $this->assertTrue(IdentityMap::with([
            '26' => new Foo
        ])->has(Foo::class, '26'));
    }

    /** @test */
    function lacking_the_object_in_the_map()
    {
        $this->assertFalse(IdentityMap::startEmpty()->has(Foo::class, 'foo'));
    }

    /** @test */
    function lacking_the_object_of_the_class()
    {
        $this->assertFalse(IdentityMap::with([
            'foo' => new Foo
        ])->has(Bar::class, 'foo'));
    }

    /** @test */
    function retrieving_the_object_from_the_map()
    {
        $foo = new Foo;
        $this->assertSame($foo, IdentityMap::with([
            'foo' => $foo
        ])->get(Foo::class, 'foo'));
    }

    /** @test */
    function retrieving_the_same_object_from_the_map_twice()
    {
        $map = IdentityMap::with([
            'foo' => new Foo
        ]);
        $this->assertSame(
            $map->get(Foo::class, 'foo'),
            $map->get(Foo::class, 'foo')
        );
    }

    /** @test */
    function differentiating_between_objects_with_different_identities()
    {
        $map = IdentityMap::with([
            'foo1' => new Foo,
            'foo2' => new Foo,
        ]);
        $this->assertEquals(
            $map->get(Foo::class, 'foo1'),
            $map->get(Foo::class, 'foo2')
        );
        $this->assertNotSame(
            $map->get(Foo::class, 'foo1'),
            $map->get(Foo::class, 'foo2')
        );
    }

    /** @test */
    function adding_the_object_to_the_map()
    {
        $map = IdentityMap::startEmpty();
        $foo = new Foo;
        $map = $map->add('foo', $foo);
        $this->assertTrue($map->has(Foo::class, 'foo'));
    }

    /** @test */
    function removing_the_object_from_the_map()
    {
        $map = IdentityMap::with([
            'foo' => new Foo
        ]);
        $map = $map->remove(Foo::class, 'foo');
        $this->assertFalse($map->has(Foo::class, 'foo'));
    }

    /** @test */
    function throwing_an_exception_when_getting_something_that_is_not_there()
    {
        $map = IdentityMap::startEmpty();

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object with id `foo` of class `' . Foo::class . '` is ' .
            'not in the identity map.'
        );

        $map->get(Foo::class, 'foo');
    }

    /** @test */
    function throwing_an_exception_when_removing_something_that_is_not_there()
    {
        $map = IdentityMap::startEmpty();

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object with id `foo` of class `' . Foo::class . '` is ' .
            'not in the identity map.'
        );

        $map->remove(Foo::class, 'foo');
    }

    /** @test */
    function throwing_an_exception_when_trying_to_add_an_object_that_was_already_there()
    {
        $map = IdentityMap::with([
            'foo' => new Foo
        ]);

        $this->expectException(AlreadyThere::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object with id `foo` of class `' . Foo::class . '` is ' .
            'already in the identity map.'
        );

        $map->add('foo', new Foo);
    }
}
