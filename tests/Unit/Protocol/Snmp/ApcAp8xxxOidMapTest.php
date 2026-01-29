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

    public function testDeviceOidModuleIndex(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::ModuleIndex, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.1.1', $oid);
    }

    public function testDeviceOidPduIndex(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::PduIndex, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.2.1', $oid);
    }

    public function testDeviceOidName(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::Name, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.3.1', $oid);
    }

    public function testDeviceOidLoadStatus(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::LoadStatus, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.4.1', $oid);
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

    public function testDeviceOidPeakPowerTimestamp(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::PeakPowerTimestamp, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.7.1', $oid);
    }

    public function testDeviceOidEnergyResetTimestamp(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::EnergyResetTimestamp, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.8.1', $oid);
    }

    public function testDeviceOidEnergy(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::Energy, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.9.1', $oid);
    }

    public function testDeviceOidEnergyStartTimestamp(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::EnergyStartTimestamp, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.10.1', $oid);
    }

    public function testDeviceOidApparentPower(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::ApparentPower, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.11.1', $oid);
    }

    public function testDeviceOidPowerFactor(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::PowerFactor, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.12.1', $oid);
    }

    public function testDeviceOidOutletCount(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::OutletCount, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.13.1', $oid);
    }

    public function testDeviceOidPhaseCount(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::PhaseCount, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.14.1', $oid);
    }

    public function testDeviceOidPeakPowerResetTimestamp(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::PeakPowerResetTimestamp, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.15.1', $oid);
    }

    public function testDeviceOidLowLoadThreshold(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::LowLoadThreshold, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.16.1', $oid);
    }

    public function testDeviceOidNearOverloadThreshold(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::NearOverloadThreshold, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.17.1', $oid);
    }

    public function testDeviceOidOverloadRestriction(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::OverloadRestriction, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.18.1', $oid);
    }

    public function testDeviceOidWithPduIndex2(): void
    {
        $oid = $this->oidMap->deviceOid(DeviceMetric::Power, 2);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.4.3.1.5.2', $oid);
    }

    public function testOutletOidModuleIndex(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::ModuleIndex, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.1.1', $oid);
    }

    public function testOutletOidPduIndex(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::PduIndex, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.2.1', $oid);
    }

    public function testOutletOidName(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::Name, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.3.1', $oid);
    }

    public function testOutletOidIndex(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::Index, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.4.1', $oid);
    }

    public function testOutletOidState(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::State, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.5.1', $oid);
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

    public function testOutletOidPeakPower(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::PeakPower, 10);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.8.10', $oid);
    }

    public function testOutletOidPeakPowerTimestamp(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::PeakPowerTimestamp, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.9.1', $oid);
    }

    public function testOutletOidEnergyResetTimestamp(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::EnergyResetTimestamp, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.10.1', $oid);
    }

    public function testOutletOidEnergy(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::Energy, 24);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.11.24', $oid);
    }

    public function testOutletOidOutletType(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::OutletType, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.12.1', $oid);
    }

    public function testOutletOidExternalLink(): void
    {
        $oid = $this->oidMap->outletOid(OutletMetric::ExternalLink, 1);
        $this->assertSame('.1.3.6.1.4.1.318.1.1.26.9.4.3.1.13.1', $oid);
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

    public function testGetDeviceDivisorApparentPower(): void
    {
        $this->assertSame(0.1, $this->oidMap->getDeviceDivisor(DeviceMetric::ApparentPower));
    }

    public function testGetDeviceDivisorPowerFactor(): void
    {
        $this->assertSame(100.0, $this->oidMap->getDeviceDivisor(DeviceMetric::PowerFactor));
    }

    public function testGetDeviceDivisorModuleIndex(): void
    {
        $this->assertSame(1.0, $this->oidMap->getDeviceDivisor(DeviceMetric::ModuleIndex));
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

    public function testGetOutletDivisorState(): void
    {
        $this->assertSame(1.0, $this->oidMap->getOutletDivisor(OutletMetric::State));
    }
}
