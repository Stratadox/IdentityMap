<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap\Test\Unit;

use Faker\Factory as Faker;
use const PHP_INT_MAX as BIGGEST_NUMBER;
use const PHP_INT_MIN as SMALLEST_NEGATIVE_NUMBER;
use PHPUnit\Framework\TestCase;
use function random_int;
use Stratadox\IdentityMap\AlreadyThere;
use Stratadox\IdentityMap\IdentityMap;
use Stratadox\IdentityMap\NoSuchObject;
use Stratadox\IdentityMap\Test\Unit\Fixture\Bar;
use Stratadox\IdentityMap\Test\Unit\Fixture\Baz;
use Stratadox\IdentityMap\Test\Unit\Fixture\Foo;

/**
 * @covers \Stratadox\IdentityMap\IdentityMap
 * @covers \Stratadox\IdentityMap\IdentityNotFound
 * @covers \Stratadox\IdentityMap\DuplicationDetected
 */
class IdentityMap_contains_entities_by_id extends TestCase
{
    /**
     * @test
     * @dataProvider randomId
     */
    function having_the_object_id_in_the_map($id)
    {
        $this->assertTrue(IdentityMap::with([
            $id => new Foo
        ])->has(Foo::class, $id));
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function having_the_object_instance_in_the_map($id)
    {
        $foo = new Foo;

        $this->assertTrue(IdentityMap::with([
            $id => $foo
        ])->hasThe($foo));
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function lacking_the_object_id_in_the_map($id)
    {
        $this->assertFalse(IdentityMap::startEmpty()->has(Foo::class, $id));
    }

    /** @test */
    function lacking_the_object_instance_in_the_map()
    {
        $this->assertFalse(IdentityMap::startEmpty()->hasThe(new Foo));
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function lacking_the_object_of_the_class($id)
    {
        $this->assertFalse(IdentityMap::with([
            $id => new Foo
        ])->has(Bar::class, $id));
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function retrieving_the_object_from_the_map($id)
    {
        $foo = new Foo;

        $this->assertSame($foo, IdentityMap::with([
            $id => $foo
        ])->get(Foo::class, $id));
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function retrieving_the_same_object_from_the_map_twice($id)
    {
        $map = IdentityMap::with([
            $id => new Foo
        ]);

        $this->assertSame(
            $map->get(Foo::class, $id),
            $map->get(Foo::class, $id)
        );
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function differentiating_between_objects_with_different_identities($id)
    {
        $map = IdentityMap::with([
            "$id:1" => new Foo,
            "$id:2" => new Foo,
        ]);

        $this->assertEquals(
            $map->get(Foo::class, "$id:1"),
            $map->get(Foo::class, "$id:2")
        );
        $this->assertNotSame(
            $map->get(Foo::class, "$id:1"),
            $map->get(Foo::class, "$id:2")
        );
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function adding_the_object_to_the_map($id)
    {
        $map = IdentityMap::startEmpty();
        $foo = new Foo;

        $map = $map->add($id, $foo);

        $this->assertTrue($map->has(Foo::class, $id));
        $this->assertTrue($map->hasThe($foo));
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function removing_the_object_from_the_map($id)
    {
        $foo1 = new Foo;
        $foo2 = new Foo;
        $map = IdentityMap::with([
            "$id:1" => $foo1,
            "$id:2" => $foo2,
        ]);

        $map = $map->remove(Foo::class, "$id:1");

        $this->assertFalse($map->has(Foo::class, "$id:1"));
        $this->assertFalse($map->hasThe($foo1));
        $this->assertTrue($map->has(Foo::class, "$id:2"));
        $this->assertTrue($map->hasThe($foo2));
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function removing_instances_from_the_map($id)
    {
        $foo1 = new Foo;
        $foo2 = new Foo;
        $map = IdentityMap::with([
            "$id:1" => $foo1,
            "$id:2" => $foo2,
        ]);

        $map = $map->removeThe($foo1);

        $this->assertFalse($map->has(Foo::class, "$id:1"));
        $this->assertFalse($map->hasThe($foo1));
        $this->assertTrue($map->has(Foo::class, "$id:2"));
        $this->assertTrue($map->hasThe($foo2));
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function retrieving_the_id_of_a_mapped_entity($id)
    {
        $foo = new Foo;
        $map = IdentityMap::with([$id => $foo]);

        $this->assertSame($id, $map->idOf($foo));
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function listing_the_registered_objects($id)
    {
        $foo[0] = new Foo;
        $foo[1] = new Foo;

        $map = IdentityMap::with([
            "$id:0" => $foo[0],
            "$id:1" => $foo[1],
        ]);

        $this->assertSame($foo, $map->objects());
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function throwing_an_exception_when_getting_something_that_is_not_there($id)
    {
        $map = IdentityMap::startEmpty();

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object with id `' . $id . '` of class `' . Foo::class . '` ' .
            'is not in the identity map.'
        );

        $map->get(Foo::class, $id);
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function throwing_an_exception_when_removing_something_that_is_not_there($id)
    {
        $map = IdentityMap::startEmpty();

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object with id `' . $id . '` of class `' . Foo::class . '` ' .
            'is not in the identity map.'
        );

        $map->remove(Foo::class, $id);
    }

    /** @test */
    function throwing_an_exception_when_removing_an_unregistered_instance()
    {
        $map = IdentityMap::startEmpty();

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object of class `' . Foo::class . '` is not in the identity map.'
        );

        $map->removeThe(new Foo);
    }

    /**
     * @test
     * @dataProvider randomId
     */
    function throwing_an_exception_when_trying_to_add_an_object_that_was_already_there($id)
    {
        $map = IdentityMap::with([
            $id => new Foo
        ]);

        $this->expectException(AlreadyThere::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object with id `' . $id . '` of class `' . Foo::class . '` ' .
            'is already in the identity map.'
        );

        $map->add($id, new Foo);
    }

    /** @test */
    function throwing_an_exception_when_trying_to_get_the_id_in_an_empty_map()
    {
        $map = IdentityMap::startEmpty();

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object of class `' . Foo::class . '` is not in the identity map.'
        );

        $map->idOf(new Foo);
    }

    /** @test */
    function throwing_an_exception_when_trying_to_get_the_id_of_an_unmapped_object()
    {
        $map = IdentityMap::with(['foo' => new Foo]);

        $this->expectException(NoSuchObject::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'The object of class `' . Foo::class . '` is not in the identity map.'
        );

        $map->idOf(new Foo);
    }

    public function randomId(): array
    {
        $random = Faker::create();
        $uuid = $random->uuid;
        $smallNumber = (string) $random->numberBetween(0, 100);
        $bigNumber = (string) $random->numberBetween(100, BIGGEST_NUMBER);
        $negativeNumber = (string) $random->numberBetween(-1, SMALLEST_NEGATIVE_NUMBER);
        $word = $random->word;
        $slug = $random->slug;
        $email = $random->email;
        $isbn = random_int(0, 1) ? $random->isbn10 : $random->isbn13;
        $url = $random->url;
        $sentence = $random->sentence;
        $macAddress = $random->macAddress;
        $compositeName = $random->firstName . ':' . $random->lastName;

        return [
            "uuid ($uuid)"                      => [$uuid],
            "small number ($smallNumber)"       => [$smallNumber],
            "big number ($bigNumber)"           => [$bigNumber],
            "negative number ($negativeNumber)" => [$negativeNumber],
            "word ($word)"                      => [$word],
            "slug ($slug)"                      => [$slug],
            "email ($email)"                    => [$email],
            "isbn ($isbn)"                      => [$isbn],
            "url ($url)"                        => [$url],
            "sentence ($sentence)"              => [$sentence],
            "mac address ($macAddress)"         => [$macAddress],
            "composite name ($compositeName)"   => [$compositeName],
        ];
    }
}
