<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Snmp;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpResponseParser;

class SnmpResponseParserTest extends TestCase
{
    private SnmpResponseParser $parser;

    protected function setUp(): void
    {
        $this->parser = new SnmpResponseParser();
    }

    public function testParseNumericInteger(): void
    {
        $this->assertSame(1234.0, $this->parser->parseNumeric('INTEGER: 1234'));
    }

    public function testParseNumericNegative(): void
    {
        $this->assertSame(-100.0, $this->parser->parseNumeric('INTEGER: -100'));
    }

    public function testParseNumericGauge(): void
    {
        $this->assertSame(5000.0, $this->parser->parseNumeric('Gauge32: 5000'));
    }

    public function testParseNumericCounter(): void
    {
        $this->assertSame(12345.0, $this->parser->parseNumeric('Counter32: 12345'));
    }

    public function testParseNumericThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse SNMP value');

        $this->parser->parseNumeric('STRING: "no numbers here"');
    }

    public function testParseStringWithQuotes(): void
    {
        $this->assertSame('Server 1', $this->parser->parseString('STRING: "Server 1"'));
    }

    public function testParseStringWithoutQuotes(): void
    {
        $this->assertSame('Server 1', $this->parser->parseString('STRING: Server 1'));
    }

    public function testParseStringTrimsWhitespace(): void
    {
        $this->assertSame('Test', $this->parser->parseString('STRING:   Test   '));
    }

    public function testParseStringEmpty(): void
    {
        $this->assertSame('', $this->parser->parseString('STRING: ""'));
    }
}
