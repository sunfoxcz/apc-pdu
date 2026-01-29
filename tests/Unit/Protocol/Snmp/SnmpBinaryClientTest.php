<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Snmp;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpBinaryClient;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpClientInterface;
use Sunfox\ApcPdu\SnmpBinaryNotFoundException;

class SnmpBinaryClientTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $client = new SnmpBinaryClient('192.168.1.100');

        $this->assertInstanceOf(SnmpClientInterface::class, $client);
    }

    public function testGetHostReturnsHost(): void
    {
        $client = new SnmpBinaryClient('192.168.1.100');

        $this->assertSame('192.168.1.100', $client->getHost());
    }

    public function testGetV1BatchEmptyArrayReturnsEmpty(): void
    {
        $client = new SnmpBinaryClient('192.168.1.100');

        $result = $client->getV1Batch([], 'public');

        $this->assertSame([], $result);
    }

    public function testGetV3BatchEmptyArrayReturnsEmpty(): void
    {
        $client = new SnmpBinaryClient('192.168.1.100');

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

    public function testSnmpBinaryNotFoundExceptionHasHelpfulMessage(): void
    {
        $exception = new SnmpBinaryNotFoundException('snmpget');

        $this->assertStringContainsString('snmpget', $exception->getMessage());
        $this->assertStringContainsString('not found', $exception->getMessage());
        $this->assertStringContainsString('apt-get install snmp', $exception->getMessage());
    }
}
