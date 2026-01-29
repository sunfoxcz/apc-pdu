<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use Sunfox\ApcPdu\PDU1;
use Sunfox\ApcPdu\PduDeviceMetric;
use PHPUnit\Framework\TestCase;

class PDU1Test extends TestCase
{
    public function testImplementsPduDeviceMetric(): void
    {
        $this->assertInstanceOf(PduDeviceMetric::class, PDU1::Power);
        $this->assertInstanceOf(PduDeviceMetric::class, PDU1::PeakPower);
        $this->assertInstanceOf(PduDeviceMetric::class, PDU1::Energy);
    }

    public function testDeviceIndexIsOne(): void
    {
        $this->assertSame(1, PDU1::Power->deviceIndex());
        $this->assertSame(1, PDU1::PeakPower->deviceIndex());
        $this->assertSame(1, PDU1::Energy->deviceIndex());
    }

    public function testOidSuffixes(): void
    {
        $this->assertSame(5, PDU1::Power->oidSuffix());
        $this->assertSame(6, PDU1::PeakPower->oidSuffix());
        $this->assertSame(9, PDU1::Energy->oidSuffix());
    }

    public function testUnits(): void
    {
        $this->assertSame('W', PDU1::Power->unit());
        $this->assertSame('W', PDU1::PeakPower->unit());
        $this->assertSame('kWh', PDU1::Energy->unit());
    }

    public function testDivisors(): void
    {
        $this->assertSame(0.1, PDU1::Power->divisor());
        $this->assertSame(0.1, PDU1::PeakPower->divisor());
        $this->assertSame(10.0, PDU1::Energy->divisor());
    }
}
