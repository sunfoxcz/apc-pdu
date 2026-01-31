<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\DeviceMetric;

class DeviceMetricTest extends TestCase
{
    public function testUnits(): void
    {
        $this->assertSame('', DeviceMetric::ModuleIndex->unit());
        $this->assertSame('', DeviceMetric::PduIndex->unit());
        $this->assertSame('', DeviceMetric::Name->unit());
        $this->assertSame('', DeviceMetric::LoadStatus->unit());
        $this->assertSame('W', DeviceMetric::Power->unit());
        $this->assertSame('W', DeviceMetric::PeakPower->unit());
        $this->assertSame('', DeviceMetric::PeakPowerTimestamp->unit());
        $this->assertSame('', DeviceMetric::PeakPowerStartTime->unit());
        $this->assertSame('kWh', DeviceMetric::Energy->unit());
        $this->assertSame('', DeviceMetric::EnergyStartTime->unit());
        $this->assertSame('VA', DeviceMetric::ApparentPower->unit());
        $this->assertSame('', DeviceMetric::PowerFactor->unit());
        $this->assertSame('', DeviceMetric::OutletCount->unit());
        $this->assertSame('', DeviceMetric::PhaseCount->unit());
        $this->assertSame('%', DeviceMetric::LowLoadThreshold->unit());
        $this->assertSame('%', DeviceMetric::NearOverloadThreshold->unit());
        $this->assertSame('', DeviceMetric::OverloadRestriction->unit());
    }

    public function testEnumValues(): void
    {
        $this->assertSame('module_index', DeviceMetric::ModuleIndex->value);
        $this->assertSame('pdu_index', DeviceMetric::PduIndex->value);
        $this->assertSame('name', DeviceMetric::Name->value);
        $this->assertSame('load_status', DeviceMetric::LoadStatus->value);
        $this->assertSame('power', DeviceMetric::Power->value);
        $this->assertSame('peak_power', DeviceMetric::PeakPower->value);
        $this->assertSame('peak_power_timestamp', DeviceMetric::PeakPowerTimestamp->value);
        $this->assertSame('peak_power_start_time', DeviceMetric::PeakPowerStartTime->value);
        $this->assertSame('energy', DeviceMetric::Energy->value);
        $this->assertSame('energy_start_time', DeviceMetric::EnergyStartTime->value);
        $this->assertSame('apparent_power', DeviceMetric::ApparentPower->value);
        $this->assertSame('power_factor', DeviceMetric::PowerFactor->value);
        $this->assertSame('outlet_count', DeviceMetric::OutletCount->value);
        $this->assertSame('phase_count', DeviceMetric::PhaseCount->value);
        $this->assertSame('low_load_threshold', DeviceMetric::LowLoadThreshold->value);
        $this->assertSame('near_overload_threshold', DeviceMetric::NearOverloadThreshold->value);
        $this->assertSame('overload_restriction', DeviceMetric::OverloadRestriction->value);
    }

    public function testIsString(): void
    {
        $this->assertFalse(DeviceMetric::ModuleIndex->isString());
        $this->assertFalse(DeviceMetric::PduIndex->isString());
        $this->assertTrue(DeviceMetric::Name->isString());
        $this->assertFalse(DeviceMetric::LoadStatus->isString());
        $this->assertFalse(DeviceMetric::Power->isString());
        $this->assertFalse(DeviceMetric::PeakPower->isString());
        $this->assertTrue(DeviceMetric::PeakPowerTimestamp->isString());
        $this->assertTrue(DeviceMetric::PeakPowerStartTime->isString());
        $this->assertFalse(DeviceMetric::Energy->isString());
        $this->assertTrue(DeviceMetric::EnergyStartTime->isString());
        $this->assertFalse(DeviceMetric::ApparentPower->isString());
        $this->assertFalse(DeviceMetric::PowerFactor->isString());
        $this->assertFalse(DeviceMetric::OutletCount->isString());
        $this->assertFalse(DeviceMetric::PhaseCount->isString());
        $this->assertFalse(DeviceMetric::LowLoadThreshold->isString());
        $this->assertFalse(DeviceMetric::NearOverloadThreshold->isString());
        $this->assertFalse(DeviceMetric::OverloadRestriction->isString());
    }

    public function testIsEnum(): void
    {
        $this->assertFalse(DeviceMetric::ModuleIndex->isEnum());
        $this->assertFalse(DeviceMetric::PduIndex->isEnum());
        $this->assertFalse(DeviceMetric::Name->isEnum());
        $this->assertTrue(DeviceMetric::LoadStatus->isEnum());
        $this->assertFalse(DeviceMetric::Power->isEnum());
        $this->assertFalse(DeviceMetric::PeakPower->isEnum());
        $this->assertFalse(DeviceMetric::PeakPowerTimestamp->isEnum());
        $this->assertFalse(DeviceMetric::PeakPowerStartTime->isEnum());
        $this->assertFalse(DeviceMetric::Energy->isEnum());
        $this->assertFalse(DeviceMetric::EnergyStartTime->isEnum());
        $this->assertFalse(DeviceMetric::ApparentPower->isEnum());
        $this->assertFalse(DeviceMetric::PowerFactor->isEnum());
        $this->assertFalse(DeviceMetric::OutletCount->isEnum());
        $this->assertFalse(DeviceMetric::PhaseCount->isEnum());
        $this->assertFalse(DeviceMetric::LowLoadThreshold->isEnum());
        $this->assertFalse(DeviceMetric::NearOverloadThreshold->isEnum());
        $this->assertFalse(DeviceMetric::OverloadRestriction->isEnum());
    }

    public function testIsInteger(): void
    {
        $this->assertTrue(DeviceMetric::ModuleIndex->isInteger());
        $this->assertTrue(DeviceMetric::PduIndex->isInteger());
        $this->assertFalse(DeviceMetric::Name->isInteger());
        $this->assertTrue(DeviceMetric::LoadStatus->isInteger());
        $this->assertFalse(DeviceMetric::Power->isInteger());
        $this->assertFalse(DeviceMetric::PeakPower->isInteger());
        $this->assertFalse(DeviceMetric::PeakPowerTimestamp->isInteger());
        $this->assertFalse(DeviceMetric::PeakPowerStartTime->isInteger());
        $this->assertFalse(DeviceMetric::Energy->isInteger());
        $this->assertFalse(DeviceMetric::EnergyStartTime->isInteger());
        $this->assertFalse(DeviceMetric::ApparentPower->isInteger());
        $this->assertFalse(DeviceMetric::PowerFactor->isInteger());
        $this->assertTrue(DeviceMetric::OutletCount->isInteger());
        $this->assertTrue(DeviceMetric::PhaseCount->isInteger());
        $this->assertTrue(DeviceMetric::LowLoadThreshold->isInteger());
        $this->assertTrue(DeviceMetric::NearOverloadThreshold->isInteger());
        $this->assertTrue(DeviceMetric::OverloadRestriction->isInteger());
    }

    public function testAllCasesExist(): void
    {
        $cases = DeviceMetric::cases();

        $this->assertCount(17, $cases);
        $this->assertContains(DeviceMetric::ModuleIndex, $cases);
        $this->assertContains(DeviceMetric::PduIndex, $cases);
        $this->assertContains(DeviceMetric::Name, $cases);
        $this->assertContains(DeviceMetric::LoadStatus, $cases);
        $this->assertContains(DeviceMetric::Power, $cases);
        $this->assertContains(DeviceMetric::PeakPower, $cases);
        $this->assertContains(DeviceMetric::PeakPowerTimestamp, $cases);
        $this->assertContains(DeviceMetric::PeakPowerStartTime, $cases);
        $this->assertContains(DeviceMetric::Energy, $cases);
        $this->assertContains(DeviceMetric::EnergyStartTime, $cases);
        $this->assertContains(DeviceMetric::ApparentPower, $cases);
        $this->assertContains(DeviceMetric::PowerFactor, $cases);
        $this->assertContains(DeviceMetric::OutletCount, $cases);
        $this->assertContains(DeviceMetric::PhaseCount, $cases);
        $this->assertContains(DeviceMetric::LowLoadThreshold, $cases);
        $this->assertContains(DeviceMetric::NearOverloadThreshold, $cases);
        $this->assertContains(DeviceMetric::OverloadRestriction, $cases);
    }
}
