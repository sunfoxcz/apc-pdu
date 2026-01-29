<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Device load status (suffix .4)
 */
enum LoadStatus: int
{
    case Normal = 1;
    case LowLoad = 2;
    case NearOverload = 3;
    case Overload = 4;
}
