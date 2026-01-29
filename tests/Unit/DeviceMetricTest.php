<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\DeviceMetric;

class DeviceMetricTest extends TestCase
{
    public function testUnits(): void
    {
        $this->assertSame('W', DeviceMetric::Power->unit());
        $this->assertSame('W', DeviceMetric::PeakPower->unit());
        $this->assertSame('kWh', DeviceMetric::Energy->unit());
    }

    public function testEnumValues(): void
    {
        $this->assertSame('power', DeviceMetric::Power->value);
        $this->assertSame('peak_power', DeviceMetric::PeakPower->value);
        $this->assertSame('energy', DeviceMetric::Energy->value);
    }

    public function testAllCasesExist(): void
    {
        $cases = DeviceMetric::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(DeviceMetric::Power, $cases);
        $this->assertContains(DeviceMetric::PeakPower, $cases);
        $this->assertContains(DeviceMetric::Energy, $cases);
    }
}
