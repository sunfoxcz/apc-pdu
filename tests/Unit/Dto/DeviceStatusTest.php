<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Dto;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Dto\DeviceStatus;

class DeviceStatusTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $status = new DeviceStatus(
            powerW: 1000.0,
            peakPowerW: 1500.0,
            energyKwh: 123.45,
        );

        $this->assertSame(1000.0, $status->powerW);
        $this->assertSame(1500.0, $status->peakPowerW);
        $this->assertSame(123.45, $status->energyKwh);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(DeviceStatus::class);

        $this->assertTrue($reflection->isReadOnly());
    }
}
