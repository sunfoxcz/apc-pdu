<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Dto;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Dto\DeviceStatus;
use Sunfox\ApcPdu\LoadStatus;

class DeviceStatusTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $status = new DeviceStatus(
            moduleIndex: 1,
            pduIndex: 1,
            name: 'PDU-1',
            loadStatus: LoadStatus::Normal,
            powerW: 1000.0,
            peakPowerW: 1500.0,
            peakPowerTimestamp: '2024-01-15 10:30:00',
            peakPowerStartTime: '2024-01-01 00:00:00',
            energyKwh: 123.45,
            energyStartTime: '2024-01-01 00:00:00',
            apparentPowerVa: 1100.0,
            powerFactor: 0.91,
            outletCount: 24,
            phaseCount: 3,
            lowLoadThreshold: 20,
            nearOverloadThreshold: 80,
            overloadRestriction: 1,
        );

        $this->assertSame(1, $status->moduleIndex);
        $this->assertSame(1, $status->pduIndex);
        $this->assertSame('PDU-1', $status->name);
        $this->assertSame(LoadStatus::Normal, $status->loadStatus);
        $this->assertSame(1000.0, $status->powerW);
        $this->assertSame(1500.0, $status->peakPowerW);
        $this->assertSame('2024-01-15 10:30:00', $status->peakPowerTimestamp);
        $this->assertSame('2024-01-01 00:00:00', $status->peakPowerStartTime);
        $this->assertSame(123.45, $status->energyKwh);
        $this->assertSame('2024-01-01 00:00:00', $status->energyStartTime);
        $this->assertSame(1100.0, $status->apparentPowerVa);
        $this->assertSame(0.91, $status->powerFactor);
        $this->assertSame(24, $status->outletCount);
        $this->assertSame(3, $status->phaseCount);
        $this->assertSame(20, $status->lowLoadThreshold);
        $this->assertSame(80, $status->nearOverloadThreshold);
        $this->assertSame(1, $status->overloadRestriction);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(DeviceStatus::class);

        $this->assertTrue($reflection->isReadOnly());
    }
}
