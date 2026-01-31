<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Dto;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Dto\DeviceStatus;
use Sunfox\ApcPdu\Dto\OutletStatus;
use Sunfox\ApcPdu\Dto\PduInfo;
use Sunfox\ApcPdu\LoadStatus;
use Sunfox\ApcPdu\PowerState;

class PduInfoTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $device = new DeviceStatus(
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
        $outlets = [
            1 => new OutletStatus(
                moduleIndex: 1,
                pduIndex: 1,
                name: 'Outlet 1',
                index: 1,
                state: PowerState::On,
                currentA: 1.0,
                powerW: 100.0,
                peakPowerW: 150.0,
                peakPowerTimestamp: '2024-01-15 10:30:00',
                peakPowerStartTime: '2024-01-01 00:00:00',
                energyKwh: 10.0,
                energyStartTime: '2024-01-01 00:00:00',
                outletType: 'IEC C13',
                externalLink: '',
            ),
            2 => new OutletStatus(
                moduleIndex: 1,
                pduIndex: 1,
                name: 'Outlet 2',
                index: 2,
                state: PowerState::On,
                currentA: 2.0,
                powerW: 200.0,
                peakPowerW: 250.0,
                peakPowerTimestamp: '2024-01-15 10:30:00',
                peakPowerStartTime: '2024-01-01 00:00:00',
                energyKwh: 20.0,
                energyStartTime: '2024-01-01 00:00:00',
                outletType: 'IEC C13',
                externalLink: '',
            ),
        ];

        $info = new PduInfo(
            pduIndex: 1,
            device: $device,
            outlets: $outlets,
        );

        $this->assertSame(1, $info->pduIndex);
        $this->assertSame($device, $info->device);
        $this->assertSame($outlets, $info->outlets);
    }

    public function testIsReadonly(): void
    {
        $reflection = new \ReflectionClass(PduInfo::class);

        $this->assertTrue($reflection->isReadOnly());
    }
}
