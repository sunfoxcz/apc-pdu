<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit;

use Sunfox\ApcPdu\ApcPdu;
use PHPUnit\Framework\TestCase;

class ApcPduTest extends TestCase
{
    public function testV1FactoryCreatesInstance(): void
    {
        $pdu = ApcPdu::v1('192.168.1.100', 'public');

        $this->assertInstanceOf(ApcPdu::class, $pdu);
        $this->assertSame('192.168.1.100', $pdu->getHost());
        $this->assertSame(24, $pdu->getOutletsPerPdu());
    }

    public function testV1FactoryWithCustomOutletsPerPdu(): void
    {
        $pdu = ApcPdu::v1('192.168.1.100', 'public', 42);

        $this->assertSame(42, $pdu->getOutletsPerPdu());
    }

    public function testV3FactoryCreatesInstance(): void
    {
        $pdu = ApcPdu::v3(
            '192.168.1.100',
            'monitor',
            'authpass',
            'privpass'
        );

        $this->assertInstanceOf(ApcPdu::class, $pdu);
        $this->assertSame('192.168.1.100', $pdu->getHost());
    }

    public function testV3FactoryWithCustomProtocols(): void
    {
        $pdu = ApcPdu::v3(
            '192.168.1.100',
            'monitor',
            'authpass',
            'privpass',
            'MD5',
            'DES',
            48
        );

        $this->assertSame(48, $pdu->getOutletsPerPdu());
    }
}
