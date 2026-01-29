<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\ApcPduOutlet;
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
abstract class AbstractSnmpIntegrationTestCase extends TestCase
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

    public function testGetOutletGetMetric(): void
    {
        $outlet = $this->pdu->getOutlet(1);

        $name = $outlet->getMetric(OutletMetric::Name);
        $this->assertIsString($name);

        $power = $outlet->getMetric(OutletMetric::Power);
        $this->assertIsFloat($power);
        $this->assertGreaterThanOrEqual(0, $power);

        $current = $outlet->getMetric(OutletMetric::Current);
        $this->assertIsFloat($current);
        $this->assertGreaterThanOrEqual(0, $current);
    }

    public function testGetOutletReturnsApcPduOutlet(): void
    {
        $outlet = $this->pdu->getOutlet(1);

        $this->assertInstanceOf(ApcPduOutlet::class, $outlet);
        $this->assertSame(1, $outlet->getOutletNumber());
        $this->assertSame(1, $outlet->getPduIndex());
    }

    public function testGetOutletGetStatus(): void
    {
        $outlet = $this->pdu->getOutlet(1);
        $status = $outlet->getStatus();

        $this->assertInstanceOf(OutletStatus::class, $status);
        $this->assertSame(1, $status->index);
        $this->assertIsString($status->name);
        $this->assertIsFloat($status->currentA);
        $this->assertIsFloat($status->powerW);
        $this->assertIsFloat($status->peakPowerW);
        $this->assertIsFloat($status->energyKwh);
    }

    public function testGetOutletIndividualMethods(): void
    {
        $outlet = $this->pdu->getOutlet(1);

        $this->assertIsString($outlet->getName());
        $this->assertIsFloat($outlet->getPower());
        $this->assertIsFloat($outlet->getCurrent());
        $this->assertIsFloat($outlet->getEnergy());
        $this->assertIsFloat($outlet->getPeakPower());
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
            $this->assertInstanceOf(ApcPduOutlet::class, $outlet);
            $this->assertSame($id, $outlet->getOutletNumber());
        }
    }

    public function testOutletIsWritable(): void
    {
        $outlet = $this->pdu->getOutlet(1);

        // All SNMP clients should support write operations
        $this->assertTrue($outlet->isWritable());
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
