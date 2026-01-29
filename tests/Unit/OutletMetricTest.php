<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\PduOutletMetric;
use PHPUnit\Framework\TestCase;

class OutletMetricTest extends TestCase
{
    public function testImplementsPduOutletMetric(): void
    {
        foreach (OutletMetric::cases() as $case) {
            $this->assertInstanceOf(PduOutletMetric::class, $case);
        }
    }

    public function testOidSuffixes(): void
    {
        $this->assertSame(3, OutletMetric::Name->oidSuffix());
        $this->assertSame(4, OutletMetric::Index->oidSuffix());
        $this->assertSame(6, OutletMetric::Current->oidSuffix());
        $this->assertSame(7, OutletMetric::Power->oidSuffix());
        $this->assertSame(8, OutletMetric::PeakPower->oidSuffix());
        $this->assertSame(11, OutletMetric::Energy->oidSuffix());
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

    public function testDivisors(): void
    {
        $this->assertSame(1.0, OutletMetric::Name->divisor());
        $this->assertSame(1.0, OutletMetric::Index->divisor());
        $this->assertSame(10.0, OutletMetric::Current->divisor());
        $this->assertSame(1.0, OutletMetric::Power->divisor());
        $this->assertSame(1.0, OutletMetric::PeakPower->divisor());
        $this->assertSame(10.0, OutletMetric::Energy->divisor());
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
}
