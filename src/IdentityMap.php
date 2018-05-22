<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap;

use function array_keys;
use function array_merge;
use function array_values;
use function assert as makeSureThat;
use function get_class as theClassOfThe;
use function is_null as weDidNotYetList;
use function is_object as itIsAn;
use function spl_object_id as theInstanceIdOf;

/**
 * Contains objects by class and id.
 *
 * @author Stratadox
 */
final class IdentityMap implements MapsObjectsByIdentity
{
    private $objectWith;
    private $entityIdFor;
    private $objects;

    private function __construct(array $objectsByClassAndId, array $idsByObject)
    {
        $this->objectWith = $objectsByClassAndId;
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
        return isset($this->objectWith[$class][$id]);
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
        return $this->objectWith[$class][$id];
    }

    /** @inheritdoc */
    public function add(string $id, object $object): MapsObjectsByIdentity
    {
        $class = theClassOfThe($object);
        $this->mayNotAlreadyHave($class, $id);

        $new = clone $this;
        $new->objectWith[$class][$id] = $object;
        $new->entityIdFor[theInstanceIdOf($object)] = $id;
        return $new;
    }

    /** @inheritdoc */
    public function remove(string $class, string $id): MapsObjectsByIdentity
    {
        $this->mustHave($class, $id);
        $entityIdFor = $this->entityIdFor;
        $objectWith = $this->objectWith;
        unset(
            $entityIdFor[theInstanceIdOf($objectWith[$class][$id])],
            $objectWith[$class][$id]
        );
        return new IdentityMap($objectWith, $entityIdFor);
    }

    /** @inheritdoc */
    public function removeThe(object $object): MapsObjectsByIdentity
    {
        return $this->remove(theClassOfThe($object), $this->idOf($object));
    }

    /** @inheritdoc */
    public function removeAllObjectsOfThe(string $class): MapsObjectsByIdentity
    {
        $objectsOf = $this->objectWith;
        if (!isset($objectsOf[$class])) {
            return $this;
        }
        $entityIdFor = $this->entityIdFor;
        foreach ($objectsOf[$class] as $object) {
            makeSureThat(itIsAn($object));
            unset($entityIdFor[theInstanceIdOf($object)]);
        }
        unset($objectsOf[$class]);
        return new IdentityMap($objectsOf, $entityIdFor);
    }

    /** @inheritdoc */
    public function idOf(object $object): string
    {
        if (!isset($this->entityIdFor[theInstanceIdOf($object)])) {
            throw IdentityNotFound::forThe($object);
        }
        return $this->entityIdFor[theInstanceIdOf($object)];
    }

    /** @inheritdoc */
    public function classes(): array
    {
        return array_keys($this->objectWith);
    }

    public function objects(): array
    {
        if (weDidNotYetList($this->objects)) {
            $objects = [];
            foreach ($this->objectWith as $class => $objectsById) {
                $objects[] = array_values($objectsById);
            }
            $this->objects = array_merge(...$objects);
        }
        return $this->objects;
    }

    /** @throws NoSuchObject */
    private function mustHave(string $class, string $id): void
    {
        if ($this->has($class, $id)) {
            return;
        }
        throw IdentityNotFound::requesting($class, $id);
    }

    /** @throws AlreadyThere */
    private function mayNotAlreadyHave(string $class, string $id): void
    {
        if ($this->has($class, $id)) {
            throw DuplicationDetected::in($class, $id);
        }
    }

    private static function addTo(
        array $objectsBy,
        string $withId,
        object $object
    ): array {
        $objectsBy[theClassOfThe($object)][$withId] = $object;
        return $objectsBy;
    }
}
