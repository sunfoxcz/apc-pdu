<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Outlet metrics (common for all PDUs)
 */
enum OutletMetric: string implements PduOutletMetric
{
    case Name = 'name';
    case Index = 'index';
    case Current = 'current';
    case Power = 'power';
    case PeakPower = 'peak_power';
    case Energy = 'energy';

    public function value(): string
    {
        return $this->value;
    }

    public function unit(): string
    {
        return match ($this) {
            self::Name, self::Index => '',
            self::Current => 'A',
            self::Power, self::PeakPower => 'W',
            self::Energy => 'kWh',
        };
    }

    public function isString(): bool
    {
        return $this === self::Name;
    }
}
