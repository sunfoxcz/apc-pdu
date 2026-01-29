<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Snmp;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\PowerState;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpClientInterface;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpV3Provider;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpWritableClientInterface;
use Sunfox\ApcPdu\Protocol\WritableProtocolProviderInterface;

class SnmpV3ProviderTest extends TestCase
{
    public function testImplementsWritableInterface(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');

        $this->assertInstanceOf(WritableProtocolProviderInterface::class, $provider);
    }

    public function testIsWritableReturnsFalseForNonWritableClient(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');

        $this->assertFalse($provider->isWritable());
    }

    public function testIsWritableReturnsTrueForWritableClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');

        $this->assertTrue($provider->isWritable());
    }

    public function testSetOutletNameThrowsForNonWritableClient(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');

        $this->expectException(PduException::class);
        $this->expectExceptionMessage('SNMP client does not support write operations');

        $provider->setOutletName(1, 5, 'New Name');
    }

    public function testSetOutletNameCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.9.4.3.1.3.5',
                's',
                'New Name',
                'monitor',
                'authPriv',
                'SHA',
                'authpass',
                'AES',
                'privpass',
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');
        $provider->setOutletName(1, 5, 'New Name');
    }

    public function testSetOutletStateOnCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.9.2.4.1.5.5',
                'i',
                '2',
                'monitor',
                'authPriv',
                'SHA',
                'authpass',
                'AES',
                'privpass',
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');
        $provider->setOutletState(1, 5, PowerState::On);
    }

    public function testSetOutletStateOffCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.9.2.4.1.5.5',
                'i',
                '1',
                'monitor',
                'authPriv',
                'SHA',
                'authpass',
                'AES',
                'privpass',
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');
        $provider->setOutletState(1, 5, PowerState::Off);
    }

    public function testSetOutletExternalLinkCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.9.4.3.1.13.5',
                's',
                'https://example.com',
                'monitor',
                'authPriv',
                'SHA',
                'authpass',
                'AES',
                'privpass',
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');
        $provider->setOutletExternalLink(1, 5, 'https://example.com');
    }

    public function testResetDevicePeakPowerThrowsForNonWritableClient(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');

        $this->expectException(PduException::class);
        $this->expectExceptionMessage('SNMP client does not support write operations');

        $provider->resetDevicePeakPower(1);
    }

    public function testResetDevicePeakPowerCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.4.1.1.10.1',
                'i',
                '2',
                'monitor',
                'authPriv',
                'SHA',
                'authpass',
                'AES',
                'privpass',
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');
        $provider->resetDevicePeakPower(1);
    }

    public function testResetDeviceEnergyCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.4.1.1.11.2',
                'i',
                '2',
                'monitor',
                'authPriv',
                'SHA',
                'authpass',
                'AES',
                'privpass',
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');
        $provider->resetDeviceEnergy(2);
    }

    public function testResetOutletsEnergyCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.4.1.1.12.1',
                'i',
                '2',
                'monitor',
                'authPriv',
                'SHA',
                'authpass',
                'AES',
                'privpass',
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');
        $provider->resetOutletsEnergy(1);
    }

    public function testResetOutletsPeakPowerCallsClient(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.4.1.1.13.3',
                'i',
                '2',
                'monitor',
                'authPriv',
                'SHA',
                'authpass',
                'AES',
                'privpass',
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');
        $provider->resetOutletsPeakPower(3);
    }

    public function testSecurityLevelAuthPriv(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                'authPriv',
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');
        $provider->resetDevicePeakPower(1);
    }

    public function testSecurityLevelAuthNoPriv(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                'authNoPriv',
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', '');
        $provider->resetDevicePeakPower(1);
    }

    public function testSetOutletNameWithNpsCalculation(): void
    {
        $client = $this->createMock(SnmpWritableClientInterface::class);
        $client->expects($this->once())
            ->method('setV3')
            ->with(
                '.1.3.6.1.4.1.318.1.1.26.9.4.3.1.3.29',
                's',
                'Test',
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                $this->anything(),
            );

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');
        // PDU 2, outlet 5 -> SNMP index = (2-1)*24 + 5 = 29
        $provider->setOutletName(2, 5, 'Test');
    }

    public function testGetHost(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $client->method('getHost')->willReturn('192.168.1.100');

        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass');

        $this->assertSame('192.168.1.100', $provider->getHost());
    }

    public function testGetOutletsPerPdu(): void
    {
        $client = $this->createMock(SnmpClientInterface::class);
        $provider = new SnmpV3Provider($client, 'monitor', 'authpass', 'privpass', 'SHA', 'AES', 42);

        $this->assertSame(42, $provider->getOutletsPerPdu());
    }
}
