<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use Sunfox\ApcPdu\DeviceMetric;
use PHPUnit\Framework\TestCase;

class DeviceMetricTest extends TestCase
{
    public function testOidSuffixes(): void
    {
        $this->assertSame(5, DeviceMetric::Power->oidSuffix());
        $this->assertSame(6, DeviceMetric::PeakPower->oidSuffix());
        $this->assertSame(9, DeviceMetric::Energy->oidSuffix());
    }

    public function testUnits(): void
    {
        $this->assertSame('W', DeviceMetric::Power->unit());
        $this->assertSame('W', DeviceMetric::PeakPower->unit());
        $this->assertSame('kWh', DeviceMetric::Energy->unit());
    }

    public function testDivisors(): void
    {
        $this->assertSame(0.1, DeviceMetric::Power->divisor());
        $this->assertSame(0.1, DeviceMetric::PeakPower->divisor());
        $this->assertSame(10.0, DeviceMetric::Energy->divisor());
    }

    public function testEnumValues(): void
    {
        $this->assertSame(5, DeviceMetric::Power->value);
        $this->assertSame(6, DeviceMetric::PeakPower->value);
        $this->assertSame(9, DeviceMetric::Energy->value);
    }
}
