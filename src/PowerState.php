<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Outlet power state (suffix .5)
 */
enum PowerState: int
{
    case Off = 1;
    case On = 2;
}
