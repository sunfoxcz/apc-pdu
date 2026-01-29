<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Dto;

final readonly class PduInfo
{
    /**
     * @param array<int, OutletStatus> $outlets
     */
    public function __construct(
        public int $pduIndex,
        public DeviceStatus $device,
        public array $outlets,
    ) {
    }
}
