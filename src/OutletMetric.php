<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Outlet metrics (common for all PDUs)
 */
enum OutletMetric: int implements PduOutletMetric
{
    case Name = 3;
    case Index = 4;
    case Current = 6;
    case Power = 7;
    case PeakPower = 8;
    case Energy = 11;

    public function oidSuffix(): int
    {
        return $this->value;
    }

    public function unit(): string
    {
        return match ($this) {
            self::Name => '',
            self::Index => '',
            self::Current => 'A',
            self::Power, self::PeakPower => 'W',
            self::Energy => 'kWh',
        };
    }

    public function divisor(): float
    {
        return match ($this) {
            self::Name, self::Index => 1,
            self::Current => 10,    // tenths of A → A
            self::Power, self::PeakPower => 1,  // directly W
            self::Energy => 10,     // tenths of kWh → kWh
        };
    }

    public function isString(): bool
    {
        return $this === self::Name;
    }
}
