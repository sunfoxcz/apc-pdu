<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Dto;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Dto\OutletStatus;

class OutletStatusTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $status = new OutletStatus(
            number: 5,
            name: 'Server 1',
            currentA: 1.5,
            powerW: 150.0,
            peakPowerW: 200.0,
            energyKwh: 50.5,
        );

        $this->assertSame(5, $status->number);
        $this->assertSame('Server 1', $status->name);
        $this->assertSame(1.5, $status->currentA);
        $this->assertSame(150.0, $status->powerW);
        $this->assertSame(200.0, $status->peakPowerW);
        $this->assertSame(50.5, $status->energyKwh);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(OutletStatus::class);

        $this->assertTrue($reflection->isReadOnly());
    }
}
