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
        $output = "devReading power\nE000: Success\n0.5 kW\n\napc>";
        $this->assertSame(500.0, $this->parser->parseDevicePower($output));
    }

    public function testParseDevicePowerWithDecimal(): void
    {
        $output = "devReading power\nE000: Success\n1.234 kW\n\napc>";
        $this->assertSame(1234.0, $this->parser->parseDevicePower($output));
    }

    public function testParseDevicePowerInWatts(): void
    {
        $output = "devReading power\nE000: Success\n1234 W\n\napc>";
        $this->assertSame(1234.0, $this->parser->parseDevicePower($output));
    }

    public function testParseDevicePowerThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse device power');

        $this->parser->parseDevicePower('Invalid output');
    }

    public function testParseDeviceEnergy(): void
    {
        $output = "devReading energy\nE000: Success\n123.45 kWh\n\napc>";
        $this->assertSame(123.45, $this->parser->parseDeviceEnergy($output));
    }

    public function testParseDeviceEnergyThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse device energy');

        $this->parser->parseDeviceEnergy('Invalid output');
    }

    public function testParseApparentPower(): void
    {
        $output = "devReading appower\nE000: Success\n0.6 kVA\n\napc>";
        $this->assertSame(600.0, $this->parser->parseApparentPower($output));
    }

    public function testParseApparentPowerInVa(): void
    {
        $output = "devReading appower\nE000: Success\n600 VA\n\napc>";
        $this->assertSame(600.0, $this->parser->parseApparentPower($output));
    }

    public function testParseApparentPowerThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse apparent power');

        $this->parser->parseApparentPower('Invalid output');
    }

    public function testParsePowerFactor(): void
    {
        $output = "devReading pf\nE000: Success\n0.85\n\napc>";
        $this->assertSame(0.85, $this->parser->parsePowerFactor($output));
    }

    public function testParsePowerFactorThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse power factor');

        $this->parser->parsePowerFactor('Invalid output');
    }

    public function testParseOutletName(): void
    {
        $output = "olName 1\n 1: Server 1\nE000: Success\n\napc>";
        $this->assertSame('Server 1', $this->parser->parseOutletName($output));
    }

    public function testParseOutletNameTrimsWhitespace(): void
    {
        $output = "olName 1\n 1:   Test Outlet   \nE000: Success\n\napc>";
        $this->assertSame('Test Outlet', $this->parser->parseOutletName($output));
    }

    public function testParseOutletNameThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse outlet name');

        $this->parser->parseOutletName('Invalid output');
    }

    public function testParseOutletCurrent(): void
    {
        $output = "olReading 1 current\n 1: sm51: 1.5 A\nE000: Success\n\napc>";
        $this->assertSame(1.5, $this->parser->parseOutletCurrent($output));
    }

    public function testParseOutletCurrentInteger(): void
    {
        $output = "olReading 1 current\n 1: sm51: 2 A\nE000: Success\n\napc>";
        $this->assertSame(2.0, $this->parser->parseOutletCurrent($output));
    }

    public function testParseOutletCurrentThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse outlet current');

        $this->parser->parseOutletCurrent('Invalid output');
    }

    public function testParseOutletPower(): void
    {
        $output = "olReading 1 power\n 1: sm51: 150 W\nE000: Success\n\napc>";
        $this->assertSame(150.0, $this->parser->parseOutletPower($output));
    }

    public function testParseOutletPowerWithDecimal(): void
    {
        $output = "olReading 1 power\n 1: sm51: 150.5 W\nE000: Success\n\napc>";
        $this->assertSame(150.5, $this->parser->parseOutletPower($output));
    }

    public function testParseOutletPowerThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse outlet power');

        $this->parser->parseOutletPower('Invalid output');
    }

    public function testParseOutletEnergy(): void
    {
        $output = "olReading 1 energy\n 1: sm51: 50.5 kWh\nE000: Success\n\napc>";
        $this->assertSame(50.5, $this->parser->parseOutletEnergy($output));
    }

    public function testParseOutletEnergyThrowsOnInvalidInput(): void
    {
        $this->expectException(PduException::class);
        $this->expectExceptionMessage('Could not parse outlet energy');

        $this->parser->parseOutletEnergy('Invalid output');
    }
}
