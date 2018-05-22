<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap;

use function get_class;
use function in_array;

/**
 * Whitelist for the Identity Map.
 *
 * Used for whitelisting entities while loading objects.
 *
 * @see IdentityMap
 *
 * @author Stratadox
 */
final class Whitelist implements MapsObjectsByIdentity
{
    private $allow;
    private $map;

    private function __construct(array $allow, MapsObjectsByIdentity $map)
    {
        $this->allow = $allow;
        $this->map = $map;
    }

    /**
     * Constructs a whitelist for the identity map.
     *
     * @param MapsObjectsByIdentity $mapped            The actual identity map.
     * @param string                ...$allowedClasses The whitelisted classes.
     * @return MapsObjectsByIdentity                   The wrapped identity map.
     */
    public static function forThe(
        MapsObjectsByIdentity $mapped,
        string ...$allowedClasses
    ): MapsObjectsByIdentity {
        $alreadyMapped = $mapped->classes();
        foreach ($alreadyMapped as $class) {
            $mapped = Whitelist::purgeIfNotIn($allowedClasses, $mapped, $class);
        }
        return new Whitelist($allowedClasses, $mapped);
    }

    /**
     * Whitelists the given classes in the identity map.
     *
     * @param string ...$classes     The whitelisted classes.
     * @return MapsObjectsByIdentity The wrapped identity map.
     */
    public static function the(string ...$classes): MapsObjectsByIdentity
    {
        return new Whitelist($classes, IdentityMap::startEmpty());
    }

    /** @inheritdoc */
    public function has(string $class, string $id): bool
    {
        return $this->map->has($class, $id);
    }

    /** @inheritdoc */
    public function hasThe(object $object): bool
    {
        return $this->map->hasThe($object);
    }

    /** @inheritdoc */
    public function get(string $class, string $id): object
    {
        return $this->map->get($class, $id);
    }

    /** @inheritdoc */
    public function idOf(object $object): string
    {
        return $this->map->idOf($object);
    }

    /** @inheritdoc */
    public function add(string $id, object $object): MapsObjectsByIdentity
    {
        if (!in_array(get_class($object), $this->allow)) {
            return $this;
        }
        return $this->newMap($this->map->add($id, $object));
    }

    /** @inheritdoc */
    public function remove(string $class, string $id): MapsObjectsByIdentity
    {
        return $this->newMap($this->map->remove($class, $id));
    }

    /** @inheritdoc */
    public function removeThe(object $object): MapsObjectsByIdentity
    {
        return $this->newMap($this->map->removeThe($object));
    }

    /** @inheritdoc */
    public function removeAllObjectsOfThe(string $class): MapsObjectsByIdentity
    {
        if (!in_array($class, $this->allow)) {
            return $this;
        }
        return $this->newMap($this->map->removeAllObjectsOfThe($class));
    }

    /** @inheritdoc */
    public function classes(): array
    {
        return $this->map->classes();
    }

    /** @inheritdoc */
    public function objects(): array
    {
        return $this->map->objects();
    }

    private static function purgeIfNotIn(
        array $allowedClasses,
        MapsObjectsByIdentity $mapped,
        string $class
    ): MapsObjectsByIdentity {
        if (in_array($class, $allowedClasses)) {
            return $mapped;
        }
        return $mapped->removeAllObjectsOfThe($class);
    }

    private function newMap(MapsObjectsByIdentity $map): MapsObjectsByIdentity
    {
        return new Whitelist($this->allow, $map);
    }
}
