<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Dto;

final readonly class DeviceStatus
{
    public function __construct(
        public float $powerW,
        public float $peakPowerW,
        public float $energyKwh,
    ) {
    }
}
