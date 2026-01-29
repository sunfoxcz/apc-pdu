<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use Sunfox\ApcPdu\PDU2;
use Sunfox\ApcPdu\PduDeviceMetric;
use PHPUnit\Framework\TestCase;

class PDU2Test extends TestCase
{
    public function testImplementsPduDeviceMetric(): void
    {
        $this->assertInstanceOf(PduDeviceMetric::class, PDU2::Power);
        $this->assertInstanceOf(PduDeviceMetric::class, PDU2::PeakPower);
        $this->assertInstanceOf(PduDeviceMetric::class, PDU2::Energy);
    }

    public function testDeviceIndexIsTwo(): void
    {
        $this->assertSame(2, PDU2::Power->deviceIndex());
        $this->assertSame(2, PDU2::PeakPower->deviceIndex());
        $this->assertSame(2, PDU2::Energy->deviceIndex());
    }

    public function testOidSuffixes(): void
    {
        $this->assertSame(5, PDU2::Power->oidSuffix());
        $this->assertSame(6, PDU2::PeakPower->oidSuffix());
        $this->assertSame(9, PDU2::Energy->oidSuffix());
    }

    public function testUnits(): void
    {
        $this->assertSame('W', PDU2::Power->unit());
        $this->assertSame('W', PDU2::PeakPower->unit());
        $this->assertSame('kWh', PDU2::Energy->unit());
    }

    public function testDivisors(): void
    {
        $this->assertSame(0.1, PDU2::Power->divisor());
        $this->assertSame(0.1, PDU2::PeakPower->divisor());
        $this->assertSame(10.0, PDU2::Energy->divisor());
    }
}
