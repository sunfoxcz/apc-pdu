<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Snmp;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\Protocol\Snmp\ApcAp8xxxOidMap;

class ApcAp8xxxOidMapTest extends TestCase
{
    private ApcAp8xxxOidMap $oidMap;

    protected function setUp(): void
    {
        $this->oidMap = new ApcAp8xxxOidMap();
    }

    public function testDeviceOidPower(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::Power, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.5.1', $oid);
    }

    public function testDeviceOidPeakPower(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::PeakPower, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.6.1', $oid);
    }

    public function testDeviceOidEnergy(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::Energy, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.9.1', $oid);
    }

    public function testDeviceOidWithPduIndex2(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::Power, 2);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.5.2', $oid);
    }

    public function testOutletOidName(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::Name, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.3.1', $oid);
    }

    public function testOutletOidCurrent(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::Current, 5);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.6.5', $oid);
    }

    public function testOutletOidPower(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::Power, 10);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.7.10', $oid);
    }

    public function testOutletOidEnergy(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::Energy, 24);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.11.24', $oid);
    }

    public function testOutletToSnmpIndexPdu1(): void
    {
        $this->assertSame(1, $this->oidMap->outletToSnmpIndex(1, 1, 24));
        $this->assertSame(24, $this->oidMap->outletToSnmpIndex(1, 24, 24));
    }

    public function testOutletToSnmpIndexPdu2(): void
    {
        $this->assertSame(25, $this->oidMap->outletToSnmpIndex(2, 1, 24));
        $this->assertSame(48, $this->oidMap->outletToSnmpIndex(2, 24, 24));
    }

    public function testOutletToSnmpIndexPdu3(): void
    {
        $this->assertSame(49, $this->oidMap->outletToSnmpIndex(3, 1, 24));
        $this->assertSame(72, $this->oidMap->outletToSnmpIndex(3, 24, 24));
    }

    public function testOutletToSnmpIndexPdu4(): void
    {
        $this->assertSame(73, $this->oidMap->outletToSnmpIndex(4, 1, 24));
        $this->assertSame(96, $this->oidMap->outletToSnmpIndex(4, 24, 24));
    }

    public function testOutletToSnmpIndexCustomOutletsPerPdu(): void
    {
        $this->assertSame(1, $this->oidMap->outletToSnmpIndex(1, 1, 42));
        $this->assertSame(43, $this->oidMap->outletToSnmpIndex(2, 1, 42));
    }

    public function testGetDeviceDivisorPower(): void
    {
        $this->assertSame(0.1, $this->oidMap->getDeviceDivisor(DeviceMetric::Power));
    }

    public function testGetDeviceDivisorPeakPower(): void
    {
        $this->assertSame(0.1, $this->oidMap->getDeviceDivisor(DeviceMetric::PeakPower));
    }

    public function testGetDeviceDivisorEnergy(): void
    {
        $this->assertSame(10.0, $this->oidMap->getDeviceDivisor(DeviceMetric::Energy));
    }

    public function testGetOutletDivisorName(): void
    {
        $this->assertSame(1.0, $this->oidMap->getOutletDivisor(OutletMetric::Name));
    }

    public function testGetOutletDivisorCurrent(): void
    {
        $this->assertSame(10.0, $this->oidMap->getOutletDivisor(OutletMetric::Current));
    }

    public function testGetOutletDivisorPower(): void
    {
        $this->assertSame(1.0, $this->oidMap->getOutletDivisor(OutletMetric::Power));
    }

    public function testGetOutletDivisorEnergy(): void
    {
        $this->assertSame(10.0, $this->oidMap->getOutletDivisor(OutletMetric::Energy));
    }
}
