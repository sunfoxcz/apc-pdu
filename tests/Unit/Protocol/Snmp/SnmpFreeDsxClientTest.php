<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Snmp;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpClientInterface;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpFreeDsxClient;
use Sunfox\ApcPdu\SnmpFreeDsxNotFoundException;

class SnmpFreeDsxClientTest extends TestCase
{
    private bool $freeDsxAvailable;

    protected function setUp(): void
    {
        $this->freeDsxAvailable = class_exists(\FreeDSx\Snmp\SnmpClient::class);
    }

    public function testConstructorThrowsExceptionWhenLibraryNotAvailable(): void
    {
        if ($this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library is installed, cannot test missing library exception.');
        }

        $this->expectException(SnmpFreeDsxNotFoundException::class);
        $this->expectExceptionMessage('FreeDSx SNMP library not found. Install it with: composer require freedsx/snmp');

        new SnmpFreeDsxClient('192.168.1.100');
    }

    public function testImplementsInterface(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $client = new SnmpFreeDsxClient('192.168.1.100');

        $this->assertInstanceOf(SnmpClientInterface::class, $client);
    }

    public function testGetHostReturnsHost(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $client = new SnmpFreeDsxClient('192.168.1.100');

        $this->assertSame('192.168.1.100', $client->getHost());
    }

    public function testGetV1BatchEmptyArrayReturnsEmpty(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $client = new SnmpFreeDsxClient('192.168.1.100');

        $result = $client->getV1Batch([], 'public');

        $this->assertSame([], $result);
    }

    public function testGetV3BatchEmptyArrayReturnsEmpty(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $client = new SnmpFreeDsxClient('192.168.1.100');

        $result = $client->getV3Batch(
            [],
            'monitor',
            'authPriv',
            'SHA',
            'authpass',
            'AES',
            'privpass',
        );

        $this->assertSame([], $result);
    }
}
