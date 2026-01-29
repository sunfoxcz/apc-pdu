<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\ApcPduOutlet;
use Sunfox\ApcPdu\Dto\OutletStatus;
use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\PowerState;
use Sunfox\ApcPdu\Protocol\ProtocolProviderInterface;
use Sunfox\ApcPdu\Protocol\WritableProtocolProviderInterface;

class ApcPduOutletTest extends TestCase
{
    public function testGetPduIndex(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);

        $outlet = new ApcPduOutlet($provider, 2, 5);

        $this->assertSame(2, $outlet->getPduIndex());
    }

    public function testGetOutletNumber(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame(5, $outlet->getOutletNumber());
    }

    public function testGetMetricDelegatesToProvider(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::Power, 1, 5)
            ->willReturn(50.0);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame(50.0, $outlet->getMetric(OutletMetric::Power));
    }

    public function testGetName(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::Name, 1, 5)
            ->willReturn('Server 1');

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame('Server 1', $outlet->getName());
    }

    public function testGetIndex(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::Index, 1, 5)
            ->willReturn(5);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame(5, $outlet->getIndex());
    }

    public function testGetState(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::State, 1, 5)
            ->willReturn(2);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame(PowerState::On, $outlet->getState());
    }

    public function testGetStateDefaultsToOffOnInvalidValue(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::State, 1, 5)
            ->willReturn(999);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame(PowerState::Off, $outlet->getState());
    }

    public function testGetCurrent(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::Current, 1, 5)
            ->willReturn(1.5);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame(1.5, $outlet->getCurrent());
    }

    public function testGetPower(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::Power, 1, 5)
            ->willReturn(150.0);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame(150.0, $outlet->getPower());
    }

    public function testGetPeakPower(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::PeakPower, 1, 5)
            ->willReturn(200.0);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame(200.0, $outlet->getPeakPower());
    }

    public function testGetEnergy(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::Energy, 1, 5)
            ->willReturn(50.5);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame(50.5, $outlet->getEnergy());
    }

    public function testGetOutletType(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::OutletType, 1, 5)
            ->willReturn('IEC C13');

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame('IEC C13', $outlet->getOutletType());
    }

    public function testGetStatusReturnsOutletStatus(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetricsBatch')
            ->with(1, 5)
            ->willReturn([
                'module_index' => 1,
                'pdu_index' => 1,
                'name' => 'Server 1',
                'index' => 5,
                'state' => 2,
                'current' => 1.5,
                'power' => 150.0,
                'peak_power' => 200.0,
                'peak_power_timestamp' => '2024-01-15 10:30:00',
                'energy_reset_timestamp' => '2024-01-01 00:00:00',
                'energy' => 50.5,
                'outlet_type' => 'IEC C13',
                'external_link' => 'https://example.com',
            ]);

        $outlet = new ApcPduOutlet($provider, 1, 5);
        $status = $outlet->getStatus();

        $this->assertInstanceOf(OutletStatus::class, $status);
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
        $this->assertSame('https://example.com', $status->externalLink);
    }

    public function testIsWritableReturnsFalseForNonWritableProvider(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertFalse($outlet->isWritable());
    }

    public function testIsWritableReturnsTrueForWritableProvider(): void
    {
        $provider = $this->createMock(WritableProtocolProviderInterface::class);
        $provider->method('isWritable')->willReturn(true);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertTrue($outlet->isWritable());
    }

    public function testIsWritableReturnsFalseWhenProviderIsWritableReturnsFalse(): void
    {
        $provider = $this->createMock(WritableProtocolProviderInterface::class);
        $provider->method('isWritable')->willReturn(false);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertFalse($outlet->isWritable());
    }

    public function testSetNameThrowsExceptionForNonWritableProvider(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Protocol does not support write operations');

        $outlet->setName('New Name');
    }

    public function testSetNameCallsProviderSetOutletName(): void
    {
        $provider = $this->createMock(WritableProtocolProviderInterface::class);
        $provider->method('isWritable')->willReturn(true);
        $provider->expects($this->once())
            ->method('setOutletName')
            ->with(1, 5, 'New Name');

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $outlet->setName('New Name');
    }

    public function testSetStateThrowsExceptionForNonWritableProvider(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Protocol does not support write operations');

        $outlet->setState(PowerState::On);
    }

    public function testSetStateCallsProviderSetOutletState(): void
    {
        $provider = $this->createMock(WritableProtocolProviderInterface::class);
        $provider->method('isWritable')->willReturn(true);
        $provider->expects($this->once())
            ->method('setOutletState')
            ->with(1, 5, PowerState::On);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $outlet->setState(PowerState::On);
    }

    public function testSetStateOffCallsProviderSetOutletState(): void
    {
        $provider = $this->createMock(WritableProtocolProviderInterface::class);
        $provider->method('isWritable')->willReturn(true);
        $provider->expects($this->once())
            ->method('setOutletState')
            ->with(1, 5, PowerState::Off);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $outlet->setState(PowerState::Off);
    }

    public function testSetExternalLinkThrowsExceptionForNonWritableProvider(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Protocol does not support write operations');

        $outlet->setExternalLink('https://example.com');
    }

    public function testSetExternalLinkCallsProviderSetOutletExternalLink(): void
    {
        $provider = $this->createMock(WritableProtocolProviderInterface::class);
        $provider->method('isWritable')->willReturn(true);
        $provider->expects($this->once())
            ->method('setOutletExternalLink')
            ->with(1, 5, 'https://example.com');

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $outlet->setExternalLink('https://example.com');
    }

    public function testGetExternalLink(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::ExternalLink, 1, 5)
            ->willReturn('https://example.com');

        $outlet = new ApcPduOutlet($provider, 1, 5);

        $this->assertSame('https://example.com', $outlet->getExternalLink());
    }
}
