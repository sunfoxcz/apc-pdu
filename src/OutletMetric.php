<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Outlet metrics (common for all PDUs)
 */
enum OutletMetric: string implements PduOutletMetric
{
    case ModuleIndex = 'module_index';
    case PduIndex = 'pdu_index';
    case Name = 'name';
    case Index = 'index';
    case State = 'state';
    case Current = 'current';
    case Power = 'power';
    case PeakPower = 'peak_power';
    case PeakPowerTimestamp = 'peak_power_timestamp';
    case EnergyResetTimestamp = 'energy_reset_timestamp';
    case Energy = 'energy';
    case OutletType = 'outlet_type';
    case ExternalLink = 'external_link';

    public function value(): string
    {
        return $this->value;
    }

    public function unit(): string
    {
        return match ($this) {
            self::ModuleIndex,
            self::PduIndex,
            self::Name,
            self::Index,
            self::State,
            self::PeakPowerTimestamp,
            self::EnergyResetTimestamp,
            self::OutletType,
            self::ExternalLink => '',
            self::Current => 'A',
            self::Power,
            self::PeakPower => 'W',
            self::Energy => 'kWh',
        };
    }

    public function isString(): bool
    {
        return match ($this) {
            self::Name,
            self::PeakPowerTimestamp,
            self::EnergyResetTimestamp,
            self::OutletType,
            self::ExternalLink => true,
            default => false,
        };
    }

    public function isEnum(): bool
    {
        return $this === self::State;
    }

    public function isInteger(): bool
    {
        return match ($this) {
            self::ModuleIndex,
            self::PduIndex,
            self::Index,
            self::State => true,
            default => false,
        };
    }
}
