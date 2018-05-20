<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap;

use function assert as makeSureThat;
use function get_class as theClassOfThe;
use function is_object as itIsAn;
use function spl_object_id as theInstanceIdOf;

/**
 * Contains objects by class and id.
 *
 * @author Stratadox
 */
final class IdentityMap implements MapsObjectsByIdentity
{
    private $objectsBy;
    private $entityIdFor;

    private function __construct(array $objectsByClassAndId, array $idsByObject)
    {
        $this->objectsBy = $objectsByClassAndId;
        $this->entityIdFor = $idsByObject;
    }

    /**
     * Produces a new identity map that contains the objects.
     *
     * @param object[] $theseObjects The objects to add, as [id => object]
     * @return MapsObjectsByIdentity The map of objects.
     */
    public static function with(array $theseObjects): MapsObjectsByIdentity
    {
        $objects = [];
        $entityIds = [];
        foreach ($theseObjects as $id => $object) {
            $objects = IdentityMap::addTo($objects, (string) $id, $object);
            $entityIds[theInstanceIdOf($object)] = (string) $id;
        }
        return new self($objects, $entityIds);
    }

    /**
     * Produces an empty identity map.
     *
     * @return MapsObjectsByIdentity The empty map of objects.
     */
    public static function startEmpty(): MapsObjectsByIdentity
    {
        return new self([], []);
    }

    /** @inheritdoc */
    public function has(string $class, string $id): bool
    {
        return isset($this->objectsBy[$class][$id]);
    }

    /** @inheritdoc */
    public function hasThe(object $object): bool
    {
        return isset($this->entityIdFor[theInstanceIdOf($object)]);
    }

    /** @inheritdoc */
    public function get(string $class, string $id): object
    {
        $this->mustHave($class, $id);
        return $this->objectsBy[$class][$id];
    }

    /** @inheritdoc */
    public function add(string $id, object $object): MapsObjectsByIdentity
    {
        $class = theClassOfThe($object);
        $this->mayNotAlreadyHave($class, $id);

        $new = clone $this;
        $new->objectsBy[$class][$id] = $object;
        $new->entityIdFor[theInstanceIdOf($object)] = $id;
        return $new;
    }

    /** @inheritdoc */
    public function remove(string $class, string $id): MapsObjectsByIdentity
    {
        $this->mustHave($class, $id);
        $entityIdFor = $this->entityIdFor;
        $objectsBy = $this->objectsBy;
        unset(
            $entityIdFor[theInstanceIdOf($objectsBy[$class][$id])],
            $objectsBy[$class][$id]
        );
        return new IdentityMap($objectsBy, $entityIdFor);
    }

    /** @inheritdoc */
    public function removeAllObjectsOfThe(string $class): MapsObjectsByIdentity
    {
        $objectsBy = $this->objectsBy;
        if (!isset($objectsBy[$class])) {
            return $this;
        }
        $entityIdFor = $this->entityIdFor;
        foreach ($objectsBy[$class] as $object) {
            makeSureThat(itIsAn($object));
            unset($entityIdFor[theInstanceIdOf($object)]);
        }
        unset($objectsBy[$class]);
        return new IdentityMap($objectsBy, $entityIdFor);
    }

    /** @inheritdoc */
    public function idOf(object $object): string
    {
        if (!isset($this->entityIdFor[theInstanceIdOf($object)])) {
            throw IdentityNotFound::forThe($object);
        }
        return $this->entityIdFor[theInstanceIdOf($object)];
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
     * @param array  $objectsBy The original map.
     * @param string $withId    The id of the object to add.
     * @param object $object    The object instance to add.
     * @return array            A new map that includes the new object.
     */
    private static function addTo(
        array $objectsBy,
        string $withId,
        object $object
    ): array {
        $objectsBy[theClassOfThe($object)][$withId] = $object;
        return $objectsBy;
    }
}
