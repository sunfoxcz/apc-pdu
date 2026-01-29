<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Snmp\SnmpFreeDsxClient;

use FreeDSx\Snmp\Module\SecurityModel\SecurityModelModuleInterface;
use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpFreeDsxClient\LenientSecurityModelModuleFactory;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpFreeDsxClient\LenientUserSecurityModelModule;

class LenientSecurityModelModuleFactoryTest extends TestCase
{
    private bool $freeDsxAvailable;

    protected function setUp(): void
    {
        $this->freeDsxAvailable = class_exists(\FreeDSx\Snmp\SnmpClient::class);
    }

    public function testGetReturnsLenientUserSecurityModelModule(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $factory = new LenientSecurityModelModuleFactory();

        // Security model 3 is USM (User-based Security Model)
        $module = $factory->get(3);

        $this->assertInstanceOf(SecurityModelModuleInterface::class, $module);
        $this->assertInstanceOf(LenientUserSecurityModelModule::class, $module);
    }

    public function testGetReturnsSameInstanceOnMultipleCalls(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $factory = new LenientSecurityModelModuleFactory();

        $module1 = $factory->get(3);
        $module2 = $factory->get(3);

        $this->assertSame($module1, $module2);
    }

    public function testGetThrowsExceptionForUnsupportedSecurityModel(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $factory = new LenientSecurityModelModuleFactory();

        $this->expectException(\FreeDSx\Snmp\Exception\ProtocolException::class);
        $this->expectExceptionMessage('The security model 999 is not supported');

        $factory->get(999);
    }
}
