<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\ApcPduFactory;
use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\Dto\DeviceStatus;
use Sunfox\ApcPdu\Dto\OutletStatus;
use Sunfox\ApcPdu\Dto\PduInfo;
use Sunfox\ApcPdu\LoadStatus;
use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\PowerState;
use Sunfox\ApcPdu\Protocol\ProtocolProviderInterface;

class ApcPduTest extends TestCase
{
    public function testFactorySnmpV1CreatesInstance(): void
    {
        $pdu = ApcPduFactory::snmpV1('192.168.1.100', 'public');

        $this->assertInstanceOf(ApcPdu::class, $pdu);
        $this->assertSame('192.168.1.100', $pdu->getHost());
        $this->assertSame(24, $pdu->getOutletsPerPdu());
    }

    public function testFactorySnmpV1WithCustomOutletsPerPdu(): void
    {
        $pdu = ApcPduFactory::snmpV1('192.168.1.100', 'public', 42);

        $this->assertSame(42, $pdu->getOutletsPerPdu());
    }

    public function testFactorySnmpV3CreatesInstance(): void
    {
        $pdu = ApcPduFactory::snmpV3(
            '192.168.1.100',
            'monitor',
            'authpass',
            'privpass',
        );

        $this->assertInstanceOf(ApcPdu::class, $pdu);
        $this->assertSame('192.168.1.100', $pdu->getHost());
    }

    public function testFactorySnmpV3WithCustomProtocols(): void
    {
        $pdu = ApcPduFactory::snmpV3(
            '192.168.1.100',
            'monitor',
            'authpass',
            'privpass',
            'MD5',
            'DES',
            48,
        );

        $this->assertSame(48, $pdu->getOutletsPerPdu());
    }

    public function testFactorySshCreatesInstance(): void
    {
        $pdu = ApcPduFactory::ssh('192.168.1.100', 'apc', 'apc');

        $this->assertInstanceOf(ApcPdu::class, $pdu);
        $this->assertSame('192.168.1.100', $pdu->getHost());
        $this->assertSame(24, $pdu->getOutletsPerPdu());
    }

    public function testGetDeviceDelegatesToProvider(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getDeviceMetric')
            ->with(DeviceMetric::Power, 1)
            ->willReturn(1234.5);

        $pdu = new ApcPdu($provider);

        $this->assertSame(1234.5, $pdu->getDevice(DeviceMetric::Power));
    }

    public function testGetDeviceStatusReturnsDto(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->method('getDeviceMetricsBatch')
            ->willReturn([
                'module_index' => 1,
                'pdu_index' => 1,
                'name' => 'PDU-1',
                'load_status' => 1,
                'power' => 1000.0,
                'peak_power' => 1500.0,
                'peak_power_timestamp' => '2024-01-15 10:30:00',
                'energy_reset_timestamp' => '2024-01-01 00:00:00',
                'energy' => 123.4,
                'energy_start_timestamp' => '2024-01-01 00:00:00',
                'apparent_power' => 1100.0,
                'power_factor' => 0.91,
                'outlet_count' => 24,
                'phase_count' => 3,
                'peak_power_reset_timestamp' => '2024-01-01 00:00:00',
                'low_load_threshold' => 20,
                'near_overload_threshold' => 80,
                'overload_restriction' => 1,
            ]);

        $pdu = new ApcPdu($provider);
        $status = $pdu->getDeviceStatus();

        $this->assertInstanceOf(DeviceStatus::class, $status);
        $this->assertSame(1, $status->moduleIndex);
        $this->assertSame(1, $status->pduIndex);
        $this->assertSame('PDU-1', $status->name);
        $this->assertSame(LoadStatus::Normal, $status->loadStatus);
        $this->assertSame(1000.0, $status->powerW);
        $this->assertSame(1500.0, $status->peakPowerW);
        $this->assertSame('2024-01-15 10:30:00', $status->peakPowerTimestamp);
        $this->assertSame('2024-01-01 00:00:00', $status->energyResetTimestamp);
        $this->assertSame(123.4, $status->energyKwh);
        $this->assertSame('2024-01-01 00:00:00', $status->energyStartTimestamp);
        $this->assertSame(1100.0, $status->apparentPowerVa);
        $this->assertSame(0.91, $status->powerFactor);
        $this->assertSame(24, $status->outletCount);
        $this->assertSame(3, $status->phaseCount);
        $this->assertSame('2024-01-01 00:00:00', $status->peakPowerResetTimestamp);
        $this->assertSame(20, $status->lowLoadThreshold);
        $this->assertSame(80, $status->nearOverloadThreshold);
        $this->assertSame(1, $status->overloadRestriction);
    }

    public function testGetOutletDelegatesToProvider(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->expects($this->once())
            ->method('getOutletMetric')
            ->with(OutletMetric::Power, 1, 5)
            ->willReturn(50.0);

        $pdu = new ApcPdu($provider);

        $this->assertSame(50.0, $pdu->getOutlet(1, 5, OutletMetric::Power));
    }

    public function testGetOutletStatusReturnsDto(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->method('getOutletMetricsBatch')
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

        $pdu = new ApcPdu($provider);
        $status = $pdu->getOutletStatus(1, 5);

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

    public function testGetAllOutletsReturnsArray(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->method('getOutletsPerPdu')->willReturn(2);
        $provider->method('getOutletMetricsBatch')
            ->willReturn([
                'module_index' => 1,
                'pdu_index' => 1,
                'name' => 'Outlet',
                'index' => 1,
                'state' => 2,
                'current' => 0.0,
                'power' => 0.0,
                'peak_power' => 0.0,
                'peak_power_timestamp' => '',
                'energy_reset_timestamp' => '',
                'energy' => 0.0,
                'outlet_type' => '',
                'external_link' => '',
            ]);

        $pdu = new ApcPdu($provider);
        $outlets = $pdu->getAllOutlets();

        $this->assertIsArray($outlets);
        $this->assertCount(2, $outlets);
        $this->assertArrayHasKey(1, $outlets);
        $this->assertArrayHasKey(2, $outlets);
        $this->assertInstanceOf(OutletStatus::class, $outlets[1]);
    }

    public function testGetAllOutletsSkipsFailingOutlets(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->method('getOutletsPerPdu')->willReturn(3);
        $provider->method('getOutletMetricsBatch')
            ->willReturnCallback(function ($pduIndex, $outletNumber) {
                if ($outletNumber === 2) {
                    throw new PduException('Outlet not available');
                }
                return [
                    'module_index' => 1,
                    'pdu_index' => 1,
                    'name' => 'Outlet',
                    'index' => $outletNumber,
                    'state' => 2,
                    'current' => 0.0,
                    'power' => 0.0,
                    'peak_power' => 0.0,
                    'peak_power_timestamp' => '',
                    'energy_reset_timestamp' => '',
                    'energy' => 0.0,
                    'outlet_type' => '',
                    'external_link' => '',
                ];
            });

        $pdu = new ApcPdu($provider);
        $outlets = $pdu->getAllOutlets();

        $this->assertCount(2, $outlets);
        $this->assertArrayHasKey(1, $outlets);
        $this->assertArrayNotHasKey(2, $outlets);
        $this->assertArrayHasKey(3, $outlets);
    }

    public function testGetPduInfoReturnsDto(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->method('getOutletsPerPdu')->willReturn(1);
        $provider->method('getDeviceMetricsBatch')
            ->willReturn([
                'module_index' => 1,
                'pdu_index' => 1,
                'name' => 'PDU-1',
                'load_status' => 1,
                'power' => 1000.0,
                'peak_power' => 1000.0,
                'peak_power_timestamp' => '2024-01-01 00:00:00',
                'energy_reset_timestamp' => '2024-01-01 00:00:00',
                'energy' => 1000.0,
                'energy_start_timestamp' => '2024-01-01 00:00:00',
                'apparent_power' => 1000.0,
                'power_factor' => 1000.0,
                'outlet_count' => 1000,
                'phase_count' => 1000,
                'peak_power_reset_timestamp' => '2024-01-01 00:00:00',
                'low_load_threshold' => 1000,
                'near_overload_threshold' => 1000,
                'overload_restriction' => 1000,
            ]);
        $provider->method('getOutletMetricsBatch')
            ->willReturn([
                'module_index' => 1,
                'pdu_index' => 1,
                'name' => 'Outlet',
                'index' => 1,
                'state' => 2,
                'current' => 0.0,
                'power' => 0.0,
                'peak_power' => 0.0,
                'peak_power_timestamp' => '',
                'energy_reset_timestamp' => '',
                'energy' => 0.0,
                'outlet_type' => '',
                'external_link' => '',
            ]);

        $pdu = new ApcPdu($provider);
        $status = $pdu->getPduInfo();

        $this->assertInstanceOf(PduInfo::class, $status);
        $this->assertSame(1, $status->pduIndex);
        $this->assertInstanceOf(DeviceStatus::class, $status->device);
        $this->assertIsArray($status->outlets);
    }

    public function testGetFullStatusReturnsMultiplePdus(): void
    {
        $callCount = 0;
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->method('getOutletsPerPdu')->willReturn(1);
        $provider->method('getDeviceMetricsBatch')
            ->willReturnCallback(function ($pduIndex) use (&$callCount) {
                $callCount++;
                // First 2 calls succeed, then fail
                if ($callCount > 2) {
                    throw new PduException('PDU not available');
                }
                return [
                    'module_index' => 1,
                    'pdu_index' => $pduIndex,
                    'name' => 'PDU',
                    'load_status' => 1,
                    'power' => 1000.0,
                    'peak_power' => 1000.0,
                    'peak_power_timestamp' => '2024-01-01 00:00:00',
                    'energy_reset_timestamp' => '2024-01-01 00:00:00',
                    'energy' => 1000.0,
                    'energy_start_timestamp' => '2024-01-01 00:00:00',
                    'apparent_power' => 1000.0,
                    'power_factor' => 1000.0,
                    'outlet_count' => 1000,
                    'phase_count' => 1000,
                    'peak_power_reset_timestamp' => '2024-01-01 00:00:00',
                    'low_load_threshold' => 1000,
                    'near_overload_threshold' => 1000,
                    'overload_restriction' => 1000,
                ];
            });
        $provider->method('getOutletMetricsBatch')
            ->willReturn([
                'module_index' => 1,
                'pdu_index' => 1,
                'name' => 'Outlet',
                'index' => 1,
                'state' => 2,
                'current' => 0.0,
                'power' => 0.0,
                'peak_power' => 0.0,
                'peak_power_timestamp' => '',
                'energy_reset_timestamp' => '',
                'energy' => 0.0,
                'outlet_type' => '',
                'external_link' => '',
            ]);

        $pdu = new ApcPdu($provider);
        $status = $pdu->getFullStatus();

        $this->assertIsArray($status);
        $this->assertCount(2, $status);
        $this->assertArrayHasKey(1, $status);
        $this->assertArrayHasKey(2, $status);
    }

    public function testTestConnectionReturnsTrue(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->method('getDeviceMetric')->willReturn(1000.0);

        $pdu = new ApcPdu($provider);

        $this->assertTrue($pdu->testConnection());
    }

    public function testTestConnectionReturnsFalse(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->method('getDeviceMetric')
            ->willThrowException(new PduException('Connection failed'));

        $pdu = new ApcPdu($provider);

        $this->assertFalse($pdu->testConnection());
    }

    public function testGetHostDelegatesToProvider(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->method('getHost')->willReturn('192.168.1.100');

        $pdu = new ApcPdu($provider);

        $this->assertSame('192.168.1.100', $pdu->getHost());
    }

    public function testGetOutletsPerPduDelegatesToProvider(): void
    {
        $provider = $this->createMock(ProtocolProviderInterface::class);
        $provider->method('getOutletsPerPdu')->willReturn(24);

        $pdu = new ApcPdu($provider);

        $this->assertSame(24, $pdu->getOutletsPerPdu());
    }
}
