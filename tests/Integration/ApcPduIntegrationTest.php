<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Integration;

use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\PDU1;
use Sunfox\ApcPdu\SnmpException;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests that require a real APC PDU device.
 *
 * Set environment variables PDU_HOST, PDU_SNMP_USER, PDU_SNMP_AUTH_PASS, PDU_SNMP_PRIV_PASS
 * to run these tests.
 *
 * @group integration
 */
class ApcPduIntegrationTest extends TestCase
{
    private ?ApcPdu $pdu = null;

    protected function setUp(): void
    {
        $host = getenv('PDU_HOST');
        $user = getenv('PDU_SNMP_USER');
        $authPass = getenv('PDU_SNMP_AUTH_PASS');
        $privPass = getenv('PDU_SNMP_PRIV_PASS');

        if (empty($host) || empty($user) || empty($authPass)) {
            $this->markTestSkipped(
                'PDU connection environment variables not set. ' .
                'Set PDU_HOST, PDU_SNMP_USER, PDU_SNMP_AUTH_PASS, and optionally PDU_SNMP_PRIV_PASS.'
            );
        }

        $this->pdu = ApcPdu::v3($host, $user, $authPass, $privPass ?: '');
    }

    public function testConnectionWorks(): void
    {
        $this->assertTrue($this->pdu->testConnection(1));
    }

    public function testGetDevicePower(): void
    {
        $power = $this->pdu->getDeviceStatus(PDU1::Power);

        $this->assertIsFloat($power);
        $this->assertGreaterThanOrEqual(0, $power);
    }

    public function testGetDevicePeakPower(): void
    {
        $peakPower = $this->pdu->getDeviceStatus(PDU1::PeakPower);

        $this->assertIsFloat($peakPower);
        $this->assertGreaterThanOrEqual(0, $peakPower);
    }

    public function testGetDeviceEnergy(): void
    {
        $energy = $this->pdu->getDeviceStatus(PDU1::Energy);

        $this->assertIsFloat($energy);
        $this->assertGreaterThanOrEqual(0, $energy);
    }

    public function testGetDeviceAll(): void
    {
        $device = $this->pdu->getDeviceAll(1);

        $this->assertArrayHasKey('power_w', $device);
        $this->assertArrayHasKey('peak_power_w', $device);
        $this->assertArrayHasKey('energy_kwh', $device);

        $this->assertIsFloat($device['power_w']);
        $this->assertIsFloat($device['peak_power_w']);
        $this->assertIsFloat($device['energy_kwh']);
    }

    public function testGetOutletName(): void
    {
        $name = $this->pdu->getOutletStatus(1, 1, OutletMetric::Name);

        $this->assertIsString($name);
    }

    public function testGetOutletPower(): void
    {
        $power = $this->pdu->getOutletStatus(1, 1, OutletMetric::Power);

        $this->assertIsFloat($power);
        $this->assertGreaterThanOrEqual(0, $power);
    }

    public function testGetOutletCurrent(): void
    {
        $current = $this->pdu->getOutletStatus(1, 1, OutletMetric::Current);

        $this->assertIsFloat($current);
        $this->assertGreaterThanOrEqual(0, $current);
    }

    public function testGetOutletAll(): void
    {
        $outlet = $this->pdu->getOutletAll(1, 1);

        $this->assertArrayHasKey('name', $outlet);
        $this->assertArrayHasKey('current_a', $outlet);
        $this->assertArrayHasKey('power_w', $outlet);
        $this->assertArrayHasKey('peak_power_w', $outlet);
        $this->assertArrayHasKey('energy_kwh', $outlet);

        $this->assertIsString($outlet['name']);
        $this->assertIsFloat($outlet['current_a']);
        $this->assertIsFloat($outlet['power_w']);
        $this->assertIsFloat($outlet['peak_power_w']);
        $this->assertIsFloat($outlet['energy_kwh']);
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
            $this->assertArrayHasKey('name', $outlet);
            $this->assertArrayHasKey('power_w', $outlet);
        }
    }

    public function testGetFullStatus(): void
    {
        $status = $this->pdu->getFullStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('pdu1', $status);
        $this->assertArrayHasKey('device', $status['pdu1']);
        $this->assertArrayHasKey('outlets', $status['pdu1']);
    }

    public function testPowerValuesAreReasonable(): void
    {
        $power = $this->pdu->getDeviceStatus(PDU1::Power);
        $peakPower = $this->pdu->getDeviceStatus(PDU1::PeakPower);

        // Power should be less than or equal to peak power
        $this->assertLessThanOrEqual(
            $peakPower,
            $power,
            'Current power should not exceed peak power'
        );

        // Power should be within reasonable range for a PDU (0 - 20000W)
        $this->assertLessThan(20000, $power, 'Power seems unreasonably high');
    }
}
