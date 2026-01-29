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
        $this->assertSame('', OutletMetric::Name->unit());
        $this->assertSame('', OutletMetric::Index->unit());
        $this->assertSame('A', OutletMetric::Current->unit());
        $this->assertSame('W', OutletMetric::Power->unit());
        $this->assertSame('W', OutletMetric::PeakPower->unit());
        $this->assertSame('kWh', OutletMetric::Energy->unit());
    }

    public function testEnumValues(): void
    {
        $this->assertSame('name', OutletMetric::Name->value);
        $this->assertSame('index', OutletMetric::Index->value);
        $this->assertSame('current', OutletMetric::Current->value);
        $this->assertSame('power', OutletMetric::Power->value);
        $this->assertSame('peak_power', OutletMetric::PeakPower->value);
        $this->assertSame('energy', OutletMetric::Energy->value);
    }

    public function testIsString(): void
    {
        $this->assertTrue(OutletMetric::Name->isString());
        $this->assertFalse(OutletMetric::Index->isString());
        $this->assertFalse(OutletMetric::Current->isString());
        $this->assertFalse(OutletMetric::Power->isString());
        $this->assertFalse(OutletMetric::PeakPower->isString());
        $this->assertFalse(OutletMetric::Energy->isString());
    }

    public function testAllCasesExist(): void
    {
        $cases = OutletMetric::cases();

        $this->assertCount(6, $cases);
        $this->assertContains(OutletMetric::Name, $cases);
        $this->assertContains(OutletMetric::Index, $cases);
        $this->assertContains(OutletMetric::Current, $cases);
        $this->assertContains(OutletMetric::Power, $cases);
        $this->assertContains(OutletMetric::PeakPower, $cases);
        $this->assertContains(OutletMetric::Energy, $cases);
    }
}
