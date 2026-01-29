<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\Dto\DeviceStatus;
use Sunfox\ApcPdu\Dto\OutletStatus;
use Sunfox\ApcPdu\Dto\PduInfo;
use Sunfox\ApcPdu\OutletMetric;

/**
 * Base class for SNMP integration tests.
 *
 * @group integration
 */
abstract class AbstractSnmpIntegrationTest extends TestCase
{
    protected ?ApcPdu $pdu = null;

    abstract protected function createPdu(
        string $host,
        string $user,
        string $authPass,
        string $privPass,
    ): ApcPdu;

    protected function setUp(): void
    {
        $host = getenv('PDU_HOST');
        $user = getenv('PDU_SNMP_USER');
        $authPass = getenv('PDU_SNMP_AUTH_PASS');
        $privPass = getenv('PDU_SNMP_PRIV_PASS');

        if (empty($host) || empty($user) || empty($authPass)) {
            $this->markTestSkipped(
                'PDU connection environment variables not set. ' .
                'Set PDU_HOST, PDU_SNMP_USER, PDU_SNMP_AUTH_PASS, and optionally PDU_SNMP_PRIV_PASS.',
            );
        }

        $this->pdu = $this->createPdu($host, $user, $authPass, $privPass ?: '');
    }

    public function testConnectionWorks(): void
    {
        $this->assertTrue($this->pdu->testConnection(1));
    }

    public function testGetDevicePower(): void
    {
        $power = $this->pdu->getDevice(DeviceMetric::Power);

        $this->assertIsFloat($power);
        $this->assertGreaterThanOrEqual(0, $power);
    }

    public function testGetDevicePeakPower(): void
    {
        $peakPower = $this->pdu->getDevice(DeviceMetric::PeakPower);

        $this->assertIsFloat($peakPower);
        $this->assertGreaterThanOrEqual(0, $peakPower);
    }

    public function testGetDeviceEnergy(): void
    {
        $energy = $this->pdu->getDevice(DeviceMetric::Energy);

        $this->assertIsFloat($energy);
        $this->assertGreaterThanOrEqual(0, $energy);
    }

    public function testGetDeviceStatus(): void
    {
        $device = $this->pdu->getDeviceStatus(1);

        $this->assertInstanceOf(DeviceStatus::class, $device);
        $this->assertIsFloat($device->powerW);
        $this->assertIsFloat($device->peakPowerW);
        $this->assertIsFloat($device->energyKwh);
        $this->assertGreaterThanOrEqual(0, $device->powerW);
        $this->assertGreaterThanOrEqual(0, $device->peakPowerW);
        $this->assertGreaterThanOrEqual(0, $device->energyKwh);
    }

    public function testGetOutletName(): void
    {
        $name = $this->pdu->getOutlet(1, 1, OutletMetric::Name);

        $this->assertIsString($name);
    }

    public function testGetOutletPower(): void
    {
        $power = $this->pdu->getOutlet(1, 1, OutletMetric::Power);

        $this->assertIsFloat($power);
        $this->assertGreaterThanOrEqual(0, $power);
    }

    public function testGetOutletCurrent(): void
    {
        $current = $this->pdu->getOutlet(1, 1, OutletMetric::Current);

        $this->assertIsFloat($current);
        $this->assertGreaterThanOrEqual(0, $current);
    }

    public function testGetOutletStatus(): void
    {
        $outlet = $this->pdu->getOutletStatus(1, 1);

        $this->assertInstanceOf(OutletStatus::class, $outlet);
        $this->assertSame(1, $outlet->index);
        $this->assertIsString($outlet->name);
        $this->assertIsFloat($outlet->currentA);
        $this->assertIsFloat($outlet->powerW);
        $this->assertIsFloat($outlet->peakPowerW);
        $this->assertIsFloat($outlet->energyKwh);
    }

    public function testGetAllOutlets(): void
    {
        $outlets = $this->pdu->getAllOutlets(1);

        $this->assertIsArray($outlets);
        $this->assertNotEmpty($outlets);

        foreach ($outlets as $id => $outlet) {
            $this->assertIsInt($id);
            $this->assertGreaterThanOrEqual(1, $id);
            $this->assertLessThanOrEqual(24, $id);
            $this->assertInstanceOf(OutletStatus::class, $outlet);
            $this->assertSame($id, $outlet->index);
        }
    }

    public function testGetPduInfo(): void
    {
        $status = $this->pdu->getPduInfo(1);

        $this->assertInstanceOf(PduInfo::class, $status);
        $this->assertSame(1, $status->pduIndex);
        $this->assertInstanceOf(DeviceStatus::class, $status->device);
        $this->assertIsArray($status->outlets);
        $this->assertNotEmpty($status->outlets);
    }

    public function testGetFullStatus(): void
    {
        $status = $this->pdu->getFullStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey(1, $status);
        $this->assertInstanceOf(PduInfo::class, $status[1]);
        $this->assertInstanceOf(DeviceStatus::class, $status[1]->device);
        $this->assertIsArray($status[1]->outlets);
    }

    public function testPowerValuesAreReasonable(): void
    {
        $power = $this->pdu->getDevice(DeviceMetric::Power);
        $peakPower = $this->pdu->getDevice(DeviceMetric::PeakPower);

        // Power should be less than or equal to peak power
        $this->assertLessThanOrEqual(
            $peakPower,
            $power,
            'Current power should not exceed peak power',
        );

        // Power should be within reasonable range for a PDU (0 - 20000W)
        $this->assertLessThan(20000, $power, 'Power seems unreasonably high');
    }

    public function testGetDeviceWithExplicitPduIndex(): void
    {
        // Test that explicit PDU index 1 works
        $power = $this->pdu->getDevice(DeviceMetric::Power, 1);

        $this->assertIsFloat($power);
        $this->assertGreaterThanOrEqual(0, $power);
    }
}
