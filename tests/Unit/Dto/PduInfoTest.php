<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Dto;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Dto\DeviceStatus;
use Sunfox\ApcPdu\Dto\OutletStatus;
use Sunfox\ApcPdu\Dto\PduInfo;

class PduInfoTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $device = new DeviceStatus(1000.0, 1500.0, 123.45);
        $outlets = [
            1 => new OutletStatus(1, 'Outlet 1', 1.0, 100.0, 150.0, 10.0),
            2 => new OutletStatus(2, 'Outlet 2', 2.0, 200.0, 250.0, 20.0),
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
