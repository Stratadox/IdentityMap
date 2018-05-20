<?php

namespace Stratadox\IdentityMap;

/**
 * Maps objects by identity.
 *
 * Contains the objects that have already been loaded.
 * Used to prevent double loading of supposedly unique entities.
 *
 * @author Stratadox
 */
interface MapsObjectsByIdentity
{
    /**
     * Checks if there is an object of this class with this id in the map.
     *
     * @param string $class The class of the object to check for.
     * @param string $id    The identity of the object, unique per class.
     * @return bool         Whether the object is in the map.
     */
    public function has(string $class, string $id): bool;

    /**
     * Checks whether the map has the object.
     *
     * @param object $object
     * @return bool
     */
    public function hasThe(object $object): bool;

    /**
     * Retrieves the object from the map.
     *
     * @param string $class The class of the object to check for.
     * @param string $id    The identity of the object, unique per class.
     * @return object       The object that was stored in the map.
     * @throws NoSuchObject When there is no object of that class with that id.
     */
    public function get(string $class, string $id): object;

    /**
     * Retrieves the id of the object.
     *
     * @param object $object The object that is in the map.
     * @return string        The identifier for the object.
     * @throws NoSuchObject  When the object is not in the map.
     */
    public function idOf(object $object): string;

    /**
     * Adds an object to the map.
     *
     * @param string $id     The identity of the object, unique per class.
     * @param object $object The object to assign to this id.
     * @return IdentityMap   A copy of the map that includes the object.
     * @throws AlreadyThere  When the object was already in the map.
     */
    public function add(string $id, object $object): MapsObjectsByIdentity;

    /**
     * Removes an object from the map.
     *
     * @param string $class The class of the object to remove.
     * @param string $id    The identity of the object, unique per class.
     * @return IdentityMap  A copy of the map excluding the object.
     * @throws NoSuchObject When there is no object of that class with that id.
     */
    public function remove(string $class, string $id): MapsObjectsByIdentity;

    /**
     * Removes all objects of a class from the map.
     *
     * @param string $class
     * @return MapsObjectsByIdentity
     */
    public function removeAllObjectsOfThe(string $class): MapsObjectsByIdentity;
}
