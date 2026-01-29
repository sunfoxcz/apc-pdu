<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Device-level metrics for APC PDU
 */
enum DeviceMetric: string
{
    case Power = 'power';
    case PeakPower = 'peak_power';
    case Energy = 'energy';

    public function unit(): string
    {
        return match ($this) {
            self::Power, self::PeakPower => 'W',
            self::Energy => 'kWh',
        };
    }
}
