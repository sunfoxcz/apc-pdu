<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Device-level metrics for APC PDU
 */
enum DeviceMetric: int
{
    case Power = 5;
    case PeakPower = 6;
    case Energy = 9;

    public function oidSuffix(): int
    {
        return $this->value;
    }

    public function unit(): string
    {
        return match ($this) {
            self::Power, self::PeakPower => 'W',
            self::Energy => 'kWh',
        };
    }

    public function divisor(): float
    {
        return match ($this) {
            self::Power, self::PeakPower => 0.1,  // hundredths of kW -> W (×10 = ÷0.1)
            self::Energy => 10,                    // tenths of kWh -> kWh
        };
    }
}
