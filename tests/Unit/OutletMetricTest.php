<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\PduOutletMetric;

class OutletMetricTest extends TestCase
{
    public function testImplementsPduOutletMetric(): void
    {
        foreach (OutletMetric::cases() as $case) {
            $this->assertInstanceOf(PduOutletMetric::class, $case);
        }
    }

    public function testUnits(): void
    {
        $this->assertSame('', OutletMetric::ModuleIndex->unit());
        $this->assertSame('', OutletMetric::PduIndex->unit());
        $this->assertSame('', OutletMetric::Name->unit());
        $this->assertSame('', OutletMetric::Index->unit());
        $this->assertSame('', OutletMetric::State->unit());
        $this->assertSame('A', OutletMetric::Current->unit());
        $this->assertSame('W', OutletMetric::Power->unit());
        $this->assertSame('W', OutletMetric::PeakPower->unit());
        $this->assertSame('', OutletMetric::PeakPowerTimestamp->unit());
        $this->assertSame('', OutletMetric::PeakPowerStartTime->unit());
        $this->assertSame('kWh', OutletMetric::Energy->unit());
        $this->assertSame('', OutletMetric::EnergyStartTime->unit());
        $this->assertSame('', OutletMetric::OutletType->unit());
        $this->assertSame('', OutletMetric::ExternalLink->unit());
    }

    public function testEnumValues(): void
    {
        $this->assertSame('module_index', OutletMetric::ModuleIndex->value);
        $this->assertSame('pdu_index', OutletMetric::PduIndex->value);
        $this->assertSame('name', OutletMetric::Name->value);
        $this->assertSame('index', OutletMetric::Index->value);
        $this->assertSame('state', OutletMetric::State->value);
        $this->assertSame('current', OutletMetric::Current->value);
        $this->assertSame('power', OutletMetric::Power->value);
        $this->assertSame('peak_power', OutletMetric::PeakPower->value);
        $this->assertSame('peak_power_timestamp', OutletMetric::PeakPowerTimestamp->value);
        $this->assertSame('peak_power_start_time', OutletMetric::PeakPowerStartTime->value);
        $this->assertSame('energy', OutletMetric::Energy->value);
        $this->assertSame('energy_start_time', OutletMetric::EnergyStartTime->value);
        $this->assertSame('outlet_type', OutletMetric::OutletType->value);
        $this->assertSame('external_link', OutletMetric::ExternalLink->value);
    }

    public function testIsString(): void
    {
        $this->assertFalse(OutletMetric::ModuleIndex->isString());
        $this->assertFalse(OutletMetric::PduIndex->isString());
        $this->assertTrue(OutletMetric::Name->isString());
        $this->assertFalse(OutletMetric::Index->isString());
        $this->assertFalse(OutletMetric::State->isString());
        $this->assertFalse(OutletMetric::Current->isString());
        $this->assertFalse(OutletMetric::Power->isString());
        $this->assertFalse(OutletMetric::PeakPower->isString());
        $this->assertTrue(OutletMetric::PeakPowerTimestamp->isString());
        $this->assertTrue(OutletMetric::PeakPowerStartTime->isString());
        $this->assertFalse(OutletMetric::Energy->isString());
        $this->assertTrue(OutletMetric::EnergyStartTime->isString());
        $this->assertTrue(OutletMetric::OutletType->isString());
        $this->assertTrue(OutletMetric::ExternalLink->isString());
    }

    public function testIsEnum(): void
    {
        $this->assertFalse(OutletMetric::ModuleIndex->isEnum());
        $this->assertFalse(OutletMetric::PduIndex->isEnum());
        $this->assertFalse(OutletMetric::Name->isEnum());
        $this->assertFalse(OutletMetric::Index->isEnum());
        $this->assertTrue(OutletMetric::State->isEnum());
        $this->assertFalse(OutletMetric::Current->isEnum());
        $this->assertFalse(OutletMetric::Power->isEnum());
        $this->assertFalse(OutletMetric::PeakPower->isEnum());
        $this->assertFalse(OutletMetric::PeakPowerTimestamp->isEnum());
        $this->assertFalse(OutletMetric::PeakPowerStartTime->isEnum());
        $this->assertFalse(OutletMetric::Energy->isEnum());
        $this->assertFalse(OutletMetric::EnergyStartTime->isEnum());
        $this->assertFalse(OutletMetric::OutletType->isEnum());
        $this->assertFalse(OutletMetric::ExternalLink->isEnum());
    }

    public function testIsInteger(): void
    {
        $this->assertTrue(OutletMetric::ModuleIndex->isInteger());
        $this->assertTrue(OutletMetric::PduIndex->isInteger());
        $this->assertFalse(OutletMetric::Name->isInteger());
        $this->assertTrue(OutletMetric::Index->isInteger());
        $this->assertTrue(OutletMetric::State->isInteger());
        $this->assertFalse(OutletMetric::Current->isInteger());
        $this->assertFalse(OutletMetric::Power->isInteger());
        $this->assertFalse(OutletMetric::PeakPower->isInteger());
        $this->assertFalse(OutletMetric::PeakPowerTimestamp->isInteger());
        $this->assertFalse(OutletMetric::PeakPowerStartTime->isInteger());
        $this->assertFalse(OutletMetric::Energy->isInteger());
        $this->assertFalse(OutletMetric::EnergyStartTime->isInteger());
        $this->assertFalse(OutletMetric::OutletType->isInteger());
        $this->assertFalse(OutletMetric::ExternalLink->isInteger());
    }

    public function testAllCasesExist(): void
    {
        $cases = OutletMetric::cases();

        $this->assertCount(14, $cases);
        $this->assertContains(OutletMetric::ModuleIndex, $cases);
        $this->assertContains(OutletMetric::PduIndex, $cases);
        $this->assertContains(OutletMetric::Name, $cases);
        $this->assertContains(OutletMetric::Index, $cases);
        $this->assertContains(OutletMetric::State, $cases);
        $this->assertContains(OutletMetric::Current, $cases);
        $this->assertContains(OutletMetric::Power, $cases);
        $this->assertContains(OutletMetric::PeakPower, $cases);
        $this->assertContains(OutletMetric::PeakPowerTimestamp, $cases);
        $this->assertContains(OutletMetric::PeakPowerStartTime, $cases);
        $this->assertContains(OutletMetric::Energy, $cases);
        $this->assertContains(OutletMetric::EnergyStartTime, $cases);
        $this->assertContains(OutletMetric::OutletType, $cases);
        $this->assertContains(OutletMetric::ExternalLink, $cases);
    }
}
