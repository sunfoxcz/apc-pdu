<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Dto;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Dto\OutletStatus;
use Sunfox\ApcPdu\PowerState;

class OutletStatusTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $status = new OutletStatus(
            moduleIndex: 1,
            pduIndex: 1,
            name: 'Server 1',
            index: 5,
            state: PowerState::On,
            currentA: 1.5,
            powerW: 150.0,
            peakPowerW: 200.0,
            peakPowerTimestamp: '2024-01-15 10:30:00',
            energyResetTimestamp: '2024-01-01 00:00:00',
            energyKwh: 50.5,
            outletType: 'IEC C13',
            externalLink: 'https://example.com/server1',
        );

        $this->assertSame(1, $status->moduleIndex);
        $this->assertSame(1, $status->pduIndex);
        $this->assertSame('Server 1', $status->name);
        $this->assertSame(5, $status->index);
        $this->assertSame(PowerState::On, $status->state);
        $this->assertSame(1.5, $status->currentA);
        $this->assertSame(150.0, $status->powerW);
        $this->assertSame(200.0, $status->peakPowerW);
        $this->assertSame('2024-01-15 10:30:00', $status->peakPowerTimestamp);
        $this->assertSame('2024-01-01 00:00:00', $status->energyResetTimestamp);
        $this->assertSame(50.5, $status->energyKwh);
        $this->assertSame('IEC C13', $status->outletType);
        $this->assertSame('https://example.com/server1', $status->externalLink);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(OutletStatus::class);

        $this->assertTrue($reflection->isReadOnly());
    }
}
