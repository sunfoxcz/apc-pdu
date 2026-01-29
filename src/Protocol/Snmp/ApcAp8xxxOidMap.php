<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\PduOutletMetric;

final class ApcAp8xxxOidMap
{
    private const OID_DEVICE_STATUS = '.1.3.6.1.4.1.318.1.1.26.4.3.1';
    private const OID_DEVICE_CONFIG = '.1.3.6.1.4.1.318.1.1.26.4.1.1';
    private const OID_OUTLET_METERED = '.1.3.6.1.4.1.318.1.1.26.9.4.3.1';
    private const OID_OUTLET_SWITCHED = '.1.3.6.1.4.1.318.1.1.26.9.2.3.1';
    private const OID_OUTLET_CONTROL = '.1.3.6.1.4.1.318.1.1.26.9.2.4.1';

    // Outlet control OID suffix
    private const OUTLET_CONTROL_COMMAND = 5;

    // Device config OID suffixes for reset operations
    private const DEVICE_CONFIG_PEAK_POWER_RESET = 10;
    private const DEVICE_CONFIG_ENERGY_RESET = 11;
    private const DEVICE_CONFIG_OUTLETS_ENERGY_RESET = 12;
    private const DEVICE_CONFIG_OUTLETS_PEAK_POWER_RESET = 13;

    private const DEVICE_OID_SUFFIX = [
        'module_index' => 1,
        'pdu_index' => 2,
        'name' => 3,
        'load_status' => 4,
        'power' => 5,
        'peak_power' => 6,
        'peak_power_timestamp' => 7,
        'energy_reset_timestamp' => 8,
        'energy' => 9,
        'energy_start_timestamp' => 10,
        'apparent_power' => 11,
        'power_factor' => 12,
        'outlet_count' => 13,
        'phase_count' => 14,
        'peak_power_reset_timestamp' => 15,
        'low_load_threshold' => 16,
        'near_overload_threshold' => 17,
        'overload_restriction' => 18,
    ];

    private const DEVICE_DIVISOR = [
        'module_index' => 1.0,
        'pdu_index' => 1.0,
        'name' => 1.0,
        'load_status' => 1.0,
        'power' => 0.1,           // hundredths kW -> W (x10)
        'peak_power' => 0.1,
        'peak_power_timestamp' => 1.0,
        'energy_reset_timestamp' => 1.0,
        'energy' => 10.0,         // tenths kWh -> kWh
        'energy_start_timestamp' => 1.0,
        'apparent_power' => 0.1,  // hundredths kVA -> VA (x10)
        'power_factor' => 100.0,  // hundredths -> ratio
        'outlet_count' => 1.0,
        'phase_count' => 1.0,
        'peak_power_reset_timestamp' => 1.0,
        'low_load_threshold' => 1.0,
        'near_overload_threshold' => 1.0,
        'overload_restriction' => 1.0,
    ];

    private const OUTLET_OID_SUFFIX = [
        'module_index' => 1,
        'pdu_index' => 2,
        'name' => 3,
        'index' => 4,
        'state' => 5,
        'current' => 6,
        'power' => 7,
        'peak_power' => 8,
        'peak_power_timestamp' => 9,
        'energy_reset_timestamp' => 10,
        'energy' => 11,
        'outlet_type' => 12,
        'external_link' => 13,
    ];

    private const OUTLET_DIVISOR = [
        'module_index' => 1.0,
        'pdu_index' => 1.0,
        'name' => 1.0,
        'index' => 1.0,
        'state' => 1.0,
        'current' => 10.0,        // tenths A -> A
        'power' => 1.0,
        'peak_power' => 1.0,
        'peak_power_timestamp' => 1.0,
        'energy_reset_timestamp' => 1.0,
        'energy' => 10.0,         // tenths kWh -> kWh
        'outlet_type' => 1.0,
        'external_link' => 1.0,
    ];

    public function deviceOid(DeviceMetric $metric, int $pduIndex): string
    {
        $suffix = self::DEVICE_OID_SUFFIX[$metric->value];

        return self::OID_DEVICE_STATUS . ".{$suffix}.{$pduIndex}";
    }

    public function devicePeakPowerResetOid(int $pduIndex): string
    {
        return self::OID_DEVICE_CONFIG . '.' . self::DEVICE_CONFIG_PEAK_POWER_RESET . ".{$pduIndex}";
    }

    public function deviceEnergyResetOid(int $pduIndex): string
    {
        return self::OID_DEVICE_CONFIG . '.' . self::DEVICE_CONFIG_ENERGY_RESET . ".{$pduIndex}";
    }

    public function outletsEnergyResetOid(int $pduIndex): string
    {
        return self::OID_DEVICE_CONFIG . '.' . self::DEVICE_CONFIG_OUTLETS_ENERGY_RESET . ".{$pduIndex}";
    }

    public function outletsPeakPowerResetOid(int $pduIndex): string
    {
        return self::OID_DEVICE_CONFIG . '.' . self::DEVICE_CONFIG_OUTLETS_PEAK_POWER_RESET . ".{$pduIndex}";
    }

    public function outletStateControlOid(int $snmpIndex): string
    {
        return self::OID_OUTLET_CONTROL . '.' . self::OUTLET_CONTROL_COMMAND . ".{$snmpIndex}";
    }

    public function outletOid(PduOutletMetric $metric, int $snmpIndex): string
    {
        $suffix = self::OUTLET_OID_SUFFIX[$metric->value()];

        // State is in switched outlet status table, not metered
        $base = $metric->value() === 'state'
            ? self::OID_OUTLET_SWITCHED
            : self::OID_OUTLET_METERED;

        return $base . ".{$suffix}.{$snmpIndex}";
    }

    public function outletToSnmpIndex(int $pduIndex, int $outletNumber, int $outletsPerPdu): int
    {
        return (($pduIndex - 1) * $outletsPerPdu) + $outletNumber;
    }

    public function getDeviceDivisor(DeviceMetric $metric): float
    {
        return self::DEVICE_DIVISOR[$metric->value];
    }

    public function getOutletDivisor(PduOutletMetric $metric): float
    {
        return self::OUTLET_DIVISOR[$metric->value()];
    }
}
