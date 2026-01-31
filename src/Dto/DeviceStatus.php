<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Dto;

use Sunfox\ApcPdu\LoadStatus;

final readonly class DeviceStatus
{
    public function __construct(
        public int $moduleIndex,
        public int $pduIndex,
        public string $name,
        public LoadStatus $loadStatus,
        public float $powerW,
        public float $peakPowerW,
        public string $peakPowerTimestamp,
        public string $peakPowerStartTime,
        public float $energyKwh,
        public string $energyStartTime,
        public float $apparentPowerVa,
        public float $powerFactor,
        public int $outletCount,
        public int $phaseCount,
        public int $lowLoadThreshold,
        public int $nearOverloadThreshold,
        public int $overloadRestriction,
    ) {
    }
}
