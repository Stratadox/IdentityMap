<?php
declare(strict_types=1);

namespace Stratadox\IdentityMap;

use InvalidArgumentException as InvalidArgument;
use function sprintf as withMessage;

final class DuplicationDetected extends InvalidArgument implements AlreadyThere
{
    /**
     * Produces an exception for when the object is already in the map.
     *
     * @param string $class The class of the proposed object.
     * @param string $id    The identity that is already there.
     * @return AlreadyThere The exception to throw.
     */
    public static function in(string $class, string $id): AlreadyThere
    {
        return new DuplicationDetected(withMessage(
            'The object with id `%s` of class `%s` is already in the identity map.',
            $id,
            $class
        ));
    }
}
