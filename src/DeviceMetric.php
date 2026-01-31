<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Device-level metrics for APC PDU
 */
enum DeviceMetric: string
{
    case ModuleIndex = 'module_index';
    case PduIndex = 'pdu_index';
    case Name = 'name';
    case LoadStatus = 'load_status';
    case Power = 'power';
    case PeakPower = 'peak_power';
    case PeakPowerTimestamp = 'peak_power_timestamp';
    case PeakPowerStartTime = 'peak_power_start_time';
    case Energy = 'energy';
    case EnergyStartTime = 'energy_start_time';
    case ApparentPower = 'apparent_power';
    case PowerFactor = 'power_factor';
    case OutletCount = 'outlet_count';
    case PhaseCount = 'phase_count';
    case LowLoadThreshold = 'low_load_threshold';
    case NearOverloadThreshold = 'near_overload_threshold';
    case OverloadRestriction = 'overload_restriction';

    public function unit(): string
    {
        return match ($this) {
            self::ModuleIndex,
            self::PduIndex,
            self::Name,
            self::LoadStatus,
            self::PeakPowerTimestamp,
            self::PeakPowerStartTime,
            self::EnergyStartTime,
            self::OutletCount,
            self::PhaseCount,
            self::OverloadRestriction => '',
            self::Power,
            self::PeakPower => 'W',
            self::Energy => 'kWh',
            self::ApparentPower => 'VA',
            self::PowerFactor => '',
            self::LowLoadThreshold,
            self::NearOverloadThreshold => '%',
        };
    }

    public function isString(): bool
    {
        return match ($this) {
            self::Name,
            self::PeakPowerTimestamp,
            self::PeakPowerStartTime,
            self::EnergyStartTime => true,
            default => false,
        };
    }

    public function isEnum(): bool
    {
        return $this === self::LoadStatus;
    }

    public function isInteger(): bool
    {
        return match ($this) {
            self::ModuleIndex,
            self::PduIndex,
            self::LoadStatus,
            self::OutletCount,
            self::PhaseCount,
            self::LowLoadThreshold,
            self::NearOverloadThreshold,
            self::OverloadRestriction => true,
            default => false,
        };
    }
}
