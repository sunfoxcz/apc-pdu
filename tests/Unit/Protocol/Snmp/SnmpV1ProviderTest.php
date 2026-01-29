<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Snmp;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\PowerState;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpClientInterface;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpV1Provider;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpWritableClientInterface;
use Sunfox\ApcPdu\Protocol\WritableProtocolProviderInterface;

class SnmpV1ProviderTest extends TestCase
{
    public function testImplementsWritableInterface(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV1Provider($client, 'public');

        $this->assertInstanceOf(WritableProtocolProviderInterface::class, $provider);
    }

    public function testIsWritableReturnsFalseForNonWritableClient(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV1Provider($client, 'public');

        $this->assertFalse($provider->isWritable());
    }

    public function testIsWritableReturnsTrueForWritableClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $provider = new SnmpV1Provider($client, 'public');

        $this->assertTrue($provider->isWritable());
    }

    public function testSetOutletNameThrowsForNonWritableClient(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV1Provider($client, 'public');

        $this->expectException(PduException::class);
        $this->expectExceptionMessage('SNMP client does not support write operations');

        $provider->setOutletName(1, 5, 'New Name');
    }

    public function testSetOutletNameCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV1')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.9.4.3.1.3.5',
                's',
                'New Name',
                'private',
            );

        $provider = new SnmpV1Provider($client, 'private');
        $provider->setOutletName(1, 5, 'New Name');
    }

    public function testSetOutletStateThrowsForNonWritableClient(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV1Provider($client, 'public');

        $this->expectException(PduException::class);
        $this->expectExceptionMessage('SNMP client does not support write operations');

        $provider->setOutletState(1, 5, PowerState::On);
    }

    public function testSetOutletStateOnCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV1')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.9.2.4.1.5.5',
                'i',
                '2',
                'private',
            );

        $provider = new SnmpV1Provider($client, 'private');
        $provider->setOutletState(1, 5, PowerState::On);
    }

    public function testSetOutletStateOffCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV1')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.9.2.4.1.5.5',
                'i',
                '1',
                'private',
            );

        $provider = new SnmpV1Provider($client, 'private');
        $provider->setOutletState(1, 5, PowerState::Off);
    }

    public function testSetOutletExternalLinkThrowsForNonWritableClient(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV1Provider($client, 'public');

        $this->expectException(PduException::class);
        $this->expectExceptionMessage('SNMP client does not support write operations');

        $provider->setOutletExternalLink(1, 5, 'https://example.com');
    }

    public function testSetOutletExternalLinkCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV1')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.9.4.3.1.13.5',
                's',
                'https://example.com',
                'private',
            );

        $provider = new SnmpV1Provider($client, 'private');
        $provider->setOutletExternalLink(1, 5, 'https://example.com');
    }

    public function testResetDevicePeakPowerThrowsForNonWritableClient(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV1Provider($client, 'public');

        $this->expectException(PduException::class);
        $this->expectExceptionMessage('SNMP client does not support write operations');

        $provider->resetDevicePeakPower(1);
    }

    public function testResetDevicePeakPowerCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV1')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.4.1.1.10.1',
                'i',
                '2',
                'private',
            );

        $provider = new SnmpV1Provider($client, 'private');
        $provider->resetDevicePeakPower(1);
    }

    public function testResetDeviceEnergyCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV1')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.4.1.1.11.2',
                'i',
                '2',
                'private',
            );

        $provider = new SnmpV1Provider($client, 'private');
        $provider->resetDeviceEnergy(2);
    }

    public function testResetOutletsEnergyCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV1')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.4.1.1.12.1',
                'i',
                '2',
                'private',
            );

        $provider = new SnmpV1Provider($client, 'private');
        $provider->resetOutletsEnergy(1);
    }

    public function testResetOutletsPeakPowerCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV1')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.4.1.1.13.3',
                'i',
                '2',
                'private',
            );

        $provider = new SnmpV1Provider($client, 'private');
        $provider->resetOutletsPeakPower(3);
    }

    public function testSetOutletNameWithNpsCalculation(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV1')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.9.4.3.1.3.29',
                's',
                'Test',
                'private',
            );

        $provider = new SnmpV1Provider($client, 'private');
        // PDU 2, outlet 5 -> SNMP index = (2-1)*24 + 5 = 29
        $provider->setOutletName(2, 5, 'Test');
    }

    public function testGetHost(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $client->method('getHost')->willReturn('192.168.1.100');

        $provider = new SnmpV1Provider($client, 'public');

        $this->assertSame('192.168.1.100', $provider->getHost());
    }

    public function testGetOutletsPerPdu(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV1Provider($client, 'public', 42);

        $this->assertSame(42, $provider->getOutletsPerPdu());
    }
}
