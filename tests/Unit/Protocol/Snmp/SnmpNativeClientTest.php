<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Snmp;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpClientInterface;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpNativeClient;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpWritableClientInterface;

class SnmpNativeClientTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $client = new SnmpNativeClient('192.168.1.100');

        $this->assertInstanceOf(SnmpClientInterface::class, $client);
    }

    public function testImplementsWritableInterface(): void
    {
        $client = new SnmpNativeClient('192.168.1.100');

        $this->assertInstanceOf(SnmpWritableClientInterface::class, $client);
    }

    public function testGetHostReturnsHost(): void
    {
        $client = new SnmpNativeClient('192.168.1.100');

        $this->assertSame('192.168.1.100', $client->getHost());
    }

    public function testGetV1BatchEmptyArrayReturnsEmpty(): void
    {
        $client = new SnmpNativeClient('192.168.1.100');

        $result = $client->getV1Batch([], 'public');

        $this->assertSame([], $result);
    }

    public function testGetV3BatchEmptyArrayReturnsEmpty(): void
    {
        $client = new SnmpNativeClient('192.168.1.100');

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
