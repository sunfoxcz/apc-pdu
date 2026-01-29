<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Ssh;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\Protocol\Ssh\ApcCliParser;

class ApcCliParserTest extends TestCase
{
    private ApcCliParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ApcCliParser();
    }

    public function testParseDevicePower(): void
    {
        $this->assertSame(1234.0, $this->parser->parseDevicePower('Power: 1234 W'));
    }

    public function testParseDevicePowerWithDecimal(): void
    {
        $this->assertSame(1234.5, $this->parser->parseDevicePower('Power: 1234.5 W'));
    }

    public function testParseDevicePowerThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse device power');

        $this->parser->parseDevicePower('Invalid output');
    }

    public function testParseDeviceEnergy(): void
    {
        $this->assertSame(123.45, $this->parser->parseDeviceEnergy('Energy: 123.45 kWh'));
    }

    public function testParseDeviceEnergyThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse device energy');

        $this->parser->parseDeviceEnergy('Invalid output');
    }

    public function testParseOutletName(): void
    {
        $this->assertSame('Server 1', $this->parser->parseOutletName("Name: Server 1\n"));
    }

    public function testParseOutletNameTrimsWhitespace(): void
    {
        $this->assertSame('Test Outlet', $this->parser->parseOutletName('Name:   Test Outlet   '));
    }

    public function testParseOutletNameThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse outlet name');

        $this->parser->parseOutletName('Invalid output');
    }

    public function testParseOutletCurrent(): void
    {
        $this->assertSame(1.5, $this->parser->parseOutletCurrent('Current: 1.5 A'));
    }

    public function testParseOutletCurrentInteger(): void
    {
        $this->assertSame(2.0, $this->parser->parseOutletCurrent('Current: 2 A'));
    }

    public function testParseOutletCurrentThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse outlet current');

        $this->parser->parseOutletCurrent('Invalid output');
    }

    public function testParseOutletPower(): void
    {
        $this->assertSame(150.0, $this->parser->parseOutletPower('Power: 150 W'));
    }

    public function testParseOutletPowerWithDecimal(): void
    {
        $this->assertSame(150.5, $this->parser->parseOutletPower('Power: 150.5 W'));
    }

    public function testParseOutletPowerThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse outlet power');

        $this->parser->parseOutletPower('Invalid output');
    }

    public function testParseOutletEnergy(): void
    {
        $this->assertSame(50.5, $this->parser->parseOutletEnergy('Energy: 50.5 kWh'));
    }

    public function testParseOutletEnergyThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse outlet energy');

        $this->parser->parseOutletEnergy('Invalid output');
    }
}
