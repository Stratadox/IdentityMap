<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap;

use function get_class as theClassOfThe;

/**
 * Contains objects by class and id.
 *
 * @author Stratadox
 */
final class IdentityMap implements MapsObjectsByIdentity
{
    private $map;

    private function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Produces a new identity map that contains the objects.
     *
     * @param array $objects         The objects to add, as [id => object]
     * @return MapsObjectsByIdentity The map of objects.
     */
    public static function with(array $objects): MapsObjectsByIdentity
    {
        $map = [];
        foreach ($objects as $id => $object) {
            $map = IdentityMap::addTo($map, $id, $object);
        }
        return new self($map);
    }

    /**
     * Produces an empty identity map.
     *
     * @return MapsObjectsByIdentity The empty map of objects.
     */
    public static function startEmpty(): MapsObjectsByIdentity
    {
        return new self([]);
    }

    /** @inheritdoc */
    public function has(string $class, string $id): bool
    {
        return isset($this->map[$class][$id]);
    }

    /** @inheritdoc */
    public function get(string $class, string $id): object
    {
        $this->mustHave($class, $id);
        return $this->map[$class][$id];
    }

    /** @inheritdoc */
    public function add(string $id, object $object): MapsObjectsByIdentity
    {
        $class = theClassOfThe($object);
        $this->mayNotAlreadyHave($class, $id);
        $map = $this->map;
        $map[$class][$id] = $object;
        return new IdentityMap($map);
    }

    /** @inheritdoc */
    public function remove(string $class, string $id): MapsObjectsByIdentity
    {
        $this->mustHave($class, $id);
        $map = $this->map;
        unset($map[$class][$id]);
        return new IdentityMap($map);
    }

    /**
     * Asserts that the object of the class with this id is present in the map.
     *
     * @param string $class The class of the object to check for.
     * @param string $id    The identity of the object, unique per class.
     * @throws NoSuchObject When there is no object with this id in the map.
     */
    private function mustHave(string $class, string $id): void
    {
        if ($this->has($class, $id)) {
            return;
        }
        throw IdentityNotFound::requesting($class, $id);
    }

    /**
     * Asserts that the object of the class with this id is not already there.
     *
     * @param string $class The class of the object to check for.
     * @param string $id    The identity of the object, unique per class.
     * @throws AlreadyThere When there is already an object with this id.
     */
    private function mayNotAlreadyHave(string $class, string $id): void
    {
        if ($this->has($class, $id)) {
            throw DuplicationDetected::in($class, $id);
        }
    }

    /**
     * Adds the object to the map, returning the new map.
     *
     * @param array  $map    The original map.
     * @param string $id     The id of the object to add.
     * @param object $object The object instance to add.
     * @return array         A new map that includes the new object.
     */
    private static function addTo(array $map, string $id, object $object): array
    {
        $map[theClassOfThe($object)][$id] = $object;
        return $map;
    }
}