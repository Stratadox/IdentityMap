<?php

namespace Stratadox\IdentityMap;

use Throwable;

/**
 * Notifies the client code that the requested object is not in the map.
 *
 * @author Stratadox
 */
interface NoSuchObject extends Throwable
{
}
