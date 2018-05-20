<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap;

/**
 * Ignore a certain class, for instance a value object.
 *
 * @author Stratadox
 */
final class Ignore implements MapsObjectsByIdentity
{
    private $ignoredClass;
    private $identityMap;

    private function __construct(string $ignoredClass, MapsObjectsByIdentity $identityMap)
    {
        $this->ignoredClass = $ignoredClass;
        $this->identityMap = $identityMap->removeAllObjectsOfThe($ignoredClass);
    }

    /**
     * Wraps the identity map with a decorator that ignores a certain class.
     *
     * @param string                $ignoredClass The class to be ignored.
     * @param MapsObjectsByIdentity $identityMap  The identity map to wrap.
     * @return Ignore                             The wrapped identity map.
     */
    public static function the(string $ignoredClass, MapsObjectsByIdentity $identityMap): MapsObjectsByIdentity
    {
        return new self($ignoredClass, $identityMap);
    }

    /** @inheritdoc */
    public function has(string $class, string $id): bool
    {
        return $this->identityMap->has($class, $id);
    }

    /** @inheritdoc */
    public function hasThe(object $object): bool
    {
        return $this->identityMap->hasThe($object);
    }

    /** @inheritdoc */
    public function get(string $class, string $id): object
    {
        return $this->identityMap->get($class, $id);
    }

    /** @inheritdoc */
    public function add(string $id, object $object): MapsObjectsByIdentity
    {
        if ($object instanceof $this->ignoredClass) {
            return $this;
        }
        return new self($this->ignoredClass, $this->identityMap->add($id, $object));
    }

    /** @inheritdoc */
    public function remove(string $class, string $id): MapsObjectsByIdentity
    {
        if ($class === $this->ignoredClass) {
            return $this;
        }
        return $this->identityMap->remove($class, $id);
    }

    /** @inheritdoc */
    public function removeThe(object $object): MapsObjectsByIdentity
    {
        return $this->identityMap->removeThe($object);
    }

    /** @inheritdoc */
    public function removeAllObjectsOfThe(string $class): MapsObjectsByIdentity
    {
        if ($class === $this->ignoredClass) {
            return $this;
        }
        return $this->identityMap->removeAllObjectsOfThe($class);
    }

    /** @inheritdoc */
    public function idOf(object $object): string
    {
        return $this->identityMap->idOf($object);
    }
}
