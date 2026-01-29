<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Dto;

final readonly class OutletStatus
{
    public function __construct(
        public int $number,
        public string $name,
        public float $currentA,
        public float $powerW,
        public float $peakPowerW,
        public float $energyKwh,
    ) {
    }
}
