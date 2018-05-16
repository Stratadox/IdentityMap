<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap;

use function get_class;
use InvalidArgumentException as InvalidArgument;
use function sprintf as withMessage;

/**
 * Notifies the client code that the requested object is not in the map.
 *
 * @author Stratadox
 */
final class IdentityNotFound extends InvalidArgument implements NoSuchObject
{
    /**
     * Produces an exception for when the object is not in the map.
     *
     * @param string $class     The class of the requested object.
     * @param string $id        The identity that is not there.
     * @return IdentityNotFound The exception to throw.
     */
    public static function requesting(string $class, string $id): self
    {
        return new IdentityNotFound(withMessage(
            'The object with id `%s` of class `%s` is not in the identity map.',
            $id,
            $class
        ));
    }

    public static function forThe(object $object): self
    {
        return new IdentityNotFound(withMessage(
            'The object of class `%s` is not in the identity map.',
            get_class($object)
        ));
    }
}
