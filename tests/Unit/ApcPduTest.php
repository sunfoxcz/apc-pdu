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
        $provider->method('getDeviceMetric')
            ->willReturnMap([
                [DeviceMetric::ModuleIndex, 1, 1],
                [DeviceMetric::PduIndex, 1, 1],
                [DeviceMetric::Name, 1, 'PDU-1'],
                [DeviceMetric::LoadStatus, 1, 1],
                [DeviceMetric::Power, 1, 1000.0],
                [DeviceMetric::PeakPower, 1, 1500.0],
                [DeviceMetric::PeakPowerTimestamp, 1, '2024-01-15 10:30:00'],
                [DeviceMetric::EnergyResetTimestamp, 1, '2024-01-01 00:00:00'],
                [DeviceMetric::Energy, 1, 123.4],
                [DeviceMetric::EnergyStartTimestamp, 1, '2024-01-01 00:00:00'],
                [DeviceMetric::ApparentPower, 1, 1100.0],
                [DeviceMetric::PowerFactor, 1, 0.91],
                [DeviceMetric::OutletCount, 1, 24],
                [DeviceMetric::PhaseCount, 1, 3],
                [DeviceMetric::PeakPowerResetTimestamp, 1, '2024-01-01 00:00:00'],
                [DeviceMetric::LowLoadThreshold, 1, 20],
                [DeviceMetric::NearOverloadThreshold, 1, 80],
                [DeviceMetric::OverloadRestriction, 1, 1],
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
        $provider->method('getOutletMetric')
            ->willReturnMap([
                [OutletMetric::ModuleIndex, 1, 5, 1],
                [OutletMetric::PduIndex, 1, 5, 1],
                [OutletMetric::Name, 1, 5, 'Server 1'],
                [OutletMetric::Index, 1, 5, 5],
                [OutletMetric::State, 1, 5, 2],
                [OutletMetric::Current, 1, 5, 1.5],
                [OutletMetric::Power, 1, 5, 150.0],
                [OutletMetric::PeakPower, 1, 5, 200.0],
                [OutletMetric::PeakPowerTimestamp, 1, 5, '2024-01-15 10:30:00'],
                [OutletMetric::EnergyResetTimestamp, 1, 5, '2024-01-01 00:00:00'],
                [OutletMetric::Energy, 1, 5, 50.5],
                [OutletMetric::OutletType, 1, 5, 'IEC C13'],
                [OutletMetric::ExternalLink, 1, 5, 'https://example.com'],
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
        $provider->method('getOutletMetric')
            ->willReturnCallback(function ($metric) {
                return match ($metric) {
                    OutletMetric::Name => 'Outlet',
                    OutletMetric::State => 2,
                    default => 0,
                };
            });

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
        $provider->method('getOutletMetric')
            ->willReturnCallback(function ($metric, $pduIndex, $outletNumber) {
                if ($outletNumber === 2) {
                    throw new PduException('Outlet not available');
                }
                return match ($metric) {
                    OutletMetric::Name => 'Outlet',
                    OutletMetric::State => 2,
                    default => 0,
                };
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
        $provider->method('getDeviceMetric')
            ->willReturnCallback(function ($metric) {
                return match ($metric) {
                    DeviceMetric::Name => 'PDU-1',
                    DeviceMetric::LoadStatus => 1,
                    DeviceMetric::PeakPowerTimestamp,
                    DeviceMetric::EnergyResetTimestamp,
                    DeviceMetric::EnergyStartTimestamp,
                    DeviceMetric::PeakPowerResetTimestamp => '2024-01-01 00:00:00',
                    default => 1000,
                };
            });
        $provider->method('getOutletMetric')
            ->willReturnCallback(function ($metric) {
                return match ($metric) {
                    OutletMetric::Name => 'Outlet',
                    OutletMetric::State => 2,
                    OutletMetric::PeakPowerTimestamp,
                    OutletMetric::EnergyResetTimestamp,
                    OutletMetric::OutletType,
                    OutletMetric::ExternalLink => '',
                    default => 0,
                };
            });

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
        $provider->method('getDeviceMetric')
            ->willReturnCallback(function ($metric) use (&$callCount) {
                $callCount++;
                // 18 metrics × 2 PDUs = 36 calls before failure
                if ($callCount > 36) {
                    throw new PduException('PDU not available');
                }
                return match ($metric) {
                    DeviceMetric::Name => 'PDU',
                    DeviceMetric::LoadStatus => 1,
                    DeviceMetric::PeakPowerTimestamp,
                    DeviceMetric::EnergyResetTimestamp,
                    DeviceMetric::EnergyStartTimestamp,
                    DeviceMetric::PeakPowerResetTimestamp => '2024-01-01 00:00:00',
                    default => 1000,
                };
            });
        $provider->method('getOutletMetric')
            ->willReturnCallback(function ($metric) {
                return match ($metric) {
                    OutletMetric::Name => 'Outlet',
                    OutletMetric::State => 2,
                    OutletMetric::PeakPowerTimestamp,
                    OutletMetric::EnergyResetTimestamp,
                    OutletMetric::OutletType,
                    OutletMetric::ExternalLink => '',
                    default => 0,
                };
            });

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
