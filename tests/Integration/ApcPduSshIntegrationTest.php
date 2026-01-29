<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\ApcPduFactory;
use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;

/**
 * Integration tests for SSH client.
 *
 * SSH has limited functionality compared to SNMP:
 * - Device metrics: Power, Energy, ApparentPower, PowerFactor
 * - Outlet metrics: Name, Current, Power, Energy
 *
 * NOTE: These tests are skipped by default due to a known ext-ssh2 issue
 * that causes segfaults during resource cleanup. Run manually if needed.
 *
 * @group integration
 * @group ssh
 */
class ApcPduSshIntegrationTest extends TestCase
{
    private ?ApcPdu $pdu = null;

    protected function setUp(): void
    {
        // Skip by default due to ext-ssh2 segfault issue during cleanup
        // Set PDU_SSH_FORCE_TEST=1 to run the tests anyway
        if (getenv('PDU_SSH_FORCE_TEST') !== '1') {
            $this->markTestSkipped(
                'SSH tests are skipped due to ext-ssh2 segfault issue during resource cleanup. ' .
                'Set PDU_SSH_FORCE_TEST=1 to run anyway.',
            );
        }

        $host = getenv('PDU_HOST');
        $user = getenv('PDU_SSH_USER');
        $pass = getenv('PDU_SSH_PASS');

        if (empty($host) || empty($user) || empty($pass)) {
            $this->markTestSkipped(
                'SSH connection environment variables not set. ' .
                'Set PDU_HOST, PDU_SSH_USER, and PDU_SSH_PASS.',
            );
        }

        if (!function_exists('ssh2_connect')) {
            $this->markTestSkipped('ext-ssh2 is not installed.');
        }

        $this->pdu = ApcPduFactory::ssh($host, $user, $pass);
    }

    public function testGetDevicePower(): void
    {
        $power = $this->pdu->getDevice(DeviceMetric::Power);

        $this->assertIsFloat($power);
        $this->assertGreaterThanOrEqual(0, $power);
    }

    public function testGetDeviceEnergy(): void
    {
        $energy = $this->pdu->getDevice(DeviceMetric::Energy);

        $this->assertIsFloat($energy);
        $this->assertGreaterThanOrEqual(0, $energy);
    }

    public function testGetDeviceApparentPower(): void
    {
        $apparentPower = $this->pdu->getDevice(DeviceMetric::ApparentPower);

        $this->assertIsFloat($apparentPower);
        $this->assertGreaterThanOrEqual(0, $apparentPower);
    }

    public function testGetDevicePowerFactor(): void
    {
        $powerFactor = $this->pdu->getDevice(DeviceMetric::PowerFactor);

        $this->assertIsFloat($powerFactor);
        $this->assertGreaterThanOrEqual(0, $powerFactor);
        $this->assertLessThanOrEqual(1, $powerFactor);
    }

    public function testGetOutletName(): void
    {
        $name = $this->pdu->getOutlet(1, 1, OutletMetric::Name);

        $this->assertIsString($name);
        $this->assertNotEmpty($name);
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

    public function testGetOutletEnergy(): void
    {
        $energy = $this->pdu->getOutlet(1, 1, OutletMetric::Energy);

        $this->assertIsFloat($energy);
        $this->assertGreaterThanOrEqual(0, $energy);
    }

    public function testPowerValuesAreReasonable(): void
    {
        $power = $this->pdu->getDevice(DeviceMetric::Power);

        // Power should be within reasonable range for a PDU (0 - 20000W)
        $this->assertLessThan(20000, $power, 'Power seems unreasonably high');
    }

    public function testGetDeviceWithExplicitPduIndex(): void
    {
        $power = $this->pdu->getDevice(DeviceMetric::Power, 1);

        $this->assertIsFloat($power);
        $this->assertGreaterThanOrEqual(0, $power);
    }
}
