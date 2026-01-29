<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Snmp;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpClient;
use Sunfox\ApcPdu\SnmpBinaryNotFoundException;

class SnmpClientTest extends TestCase
{
    public function testGetHostReturnsHost(): void
    {
        $client = new SnmpClient('192.168.1.100');

        $this->assertSame('192.168.1.100', $client->getHost());
    }

    public function testGetV1BatchEmptyArrayReturnsEmpty(): void
    {
        $client = new SnmpClient('192.168.1.100');

        $result = $client->getV1Batch([], 'public');

        $this->assertSame([], $result);
    }

    public function testGetV3BatchEmptyArrayReturnsEmpty(): void
    {
        $client = new SnmpClient('192.168.1.100');

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
