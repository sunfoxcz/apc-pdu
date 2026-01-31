<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Dto;

use Sunfox\ApcPdu\PowerState;

final readonly class OutletStatus
{
    public function __construct(
        public int $moduleIndex,
        public int $pduIndex,
        public string $name,
        public int $index,
        public PowerState $state,
        public float $currentA,
        public float $powerW,
        public float $peakPowerW,
        public string $peakPowerTimestamp,
        public string $peakPowerStartTime,
        public float $energyKwh,
        public string $energyStartTime,
        public string $outletType,
        public string $externalLink,
    ) {
    }
}
