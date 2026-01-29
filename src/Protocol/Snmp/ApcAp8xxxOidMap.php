<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\PduOutletMetric;

final class ApcAp8xxxOidMap
{
    private const OID_DEVICE = '.1.3.6.1.4.1.318.1.1.26.4.3.1';
    private const OID_OUTLET = '.1.3.6.1.4.1.318.1.1.26.9.4.3.1';

    private const DEVICE_OID_SUFFIX = [
        'power' => 5,
        'peak_power' => 6,
        'energy' => 9,
    ];

    private const DEVICE_DIVISOR = [
        'power' => 0.1,       // hundredths kW -> W (x10)
        'peak_power' => 0.1,
        'energy' => 10.0,     // tenths kWh -> kWh
    ];

    private const OUTLET_OID_SUFFIX = [
        'name' => 3,
        'index' => 4,
        'current' => 6,
        'power' => 7,
        'peak_power' => 8,
        'energy' => 11,
    ];

    private const OUTLET_DIVISOR = [
        'name' => 1.0,
        'index' => 1.0,
        'current' => 10.0,    // tenths A -> A
        'power' => 1.0,
        'peak_power' => 1.0,
        'energy' => 10.0,     // tenths kWh -> kWh
    ];

    public function deviceOid(DeviceMetric $metric, int $pduIndex): string
    {
        $suffix = self::DEVICE_OID_SUFFIX[$metric->value];

        return self::OID_DEVICE . ".{$suffix}.{$pduIndex}";
    }

    public function outletOid(PduOutletMetric $metric, int $snmpIndex): string
    {
        $suffix = self::OUTLET_OID_SUFFIX[$metric->value()];

        return self::OID_OUTLET . ".{$suffix}.{$snmpIndex}";
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
