<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Outlet control command values for rPDU2OutletSwitchedControlCommand (suffix .5)
 */
enum OutletCommand: int
{
    case On = 1;
    case Off = 2;
    case Reboot = 3;
}
