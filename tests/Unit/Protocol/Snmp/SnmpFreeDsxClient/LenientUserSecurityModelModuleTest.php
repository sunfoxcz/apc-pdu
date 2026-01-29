<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Unit\Protocol\Snmp\SnmpFreeDsxClient;

use FreeDSx\Snmp\Exception\SecurityModelException;
use FreeDSx\Snmp\Message\AbstractMessageV3;
use FreeDSx\Snmp\Message\MessageHeader;
use FreeDSx\Snmp\Message\Response\MessageResponseV3;
use FreeDSx\Snmp\Message\Security\UsmSecurityParameters;
use FreeDSx\Snmp\Module\SecurityModel\UserSecurityModelModule;
use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpFreeDsxClient\LenientUserSecurityModelModule;

class LenientUserSecurityModelModuleTest extends TestCase
{
    private bool $freeDsxAvailable;

    protected function setUp(): void
    {
        $this->freeDsxAvailable = class_exists(\FreeDSx\Snmp\SnmpClient::class);
    }

    public function testExtendsUserSecurityModelModule(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $module = new LenientUserSecurityModelModule();

        $this->assertInstanceOf(UserSecurityModelModule::class, $module);
    }

    public function testSupportsReturnsThree(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        // USM security model is 3
        $this->assertSame(3, LenientUserSecurityModelModule::supports());
    }

    public function testHandleIncomingMessageThrowsWhenSecurityParametersMissing(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $module = new LenientUserSecurityModelModule();

        $header = $this->createMock(MessageHeader::class);
        $message = $this->createMock(AbstractMessageV3::class);
        $message->method('getSecurityParameters')->willReturn(null);
        $message->method('getMessageHeader')->willReturn($header);

        $this->expectException(SecurityModelException::class);
        $this->expectExceptionMessage('The received SNMP message is missing the security parameters.');

        $module->handleIncomingMessage($message, [
            'use_auth' => true,
            'use_priv' => true,
        ]);
    }

    public function testHandleIncomingMessageDoesNotThrowWhenHeaderLacksAuthFlag(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $module = new LenientUserSecurityModelModule();

        // Create a header that has NO authentication flag set
        $header = $this->createMock(MessageHeader::class);
        $header->method('hasAuthentication')->willReturn(false);
        $header->method('hasPrivacy')->willReturn(false);

        $securityParams = $this->createMock(UsmSecurityParameters::class);

        // Use a non-response message to avoid validation logic
        $message = $this->createMock(AbstractMessageV3::class);
        $message->method('getSecurityParameters')->willReturn($securityParams);
        $message->method('getMessageHeader')->willReturn($header);

        // The key difference from the parent class: this should NOT throw
        // "Authentication was requested, but the received header has none specified."
        $result = $module->handleIncomingMessage($message, [
            'use_auth' => true,
            'use_priv' => false,
            'auth_mech' => 'sha1',
            'auth_pwd' => 'password',
        ]);

        // If we get here without exception, the lenient behavior is working
        $this->assertSame($message, $result);
    }

    public function testHandleIncomingMessageDoesNotThrowWhenHeaderLacksPrivFlag(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $module = new LenientUserSecurityModelModule();

        // Create a header that has NO privacy flag set
        $header = $this->createMock(MessageHeader::class);
        $header->method('hasAuthentication')->willReturn(false);
        $header->method('hasPrivacy')->willReturn(false);

        $securityParams = $this->createMock(UsmSecurityParameters::class);

        // Use a non-response message to avoid validation logic
        $message = $this->createMock(AbstractMessageV3::class);
        $message->method('getSecurityParameters')->willReturn($securityParams);
        $message->method('getMessageHeader')->willReturn($header);

        // This should NOT throw "Privacy was requested, but the received header has none specified."
        $result = $module->handleIncomingMessage($message, [
            'use_auth' => true,
            'use_priv' => true,
            'auth_mech' => 'sha1',
            'auth_pwd' => 'password',
            'priv_mech' => 'aes128',
            'priv_pwd' => 'password',
        ]);

        // If we get here without exception, the lenient behavior is working
        $this->assertSame($message, $result);
    }

    public function testHandleIncomingMessagePassesThroughWhenNoAuthRequired(): void
    {
        if (!$this->freeDsxAvailable) {
            $this->markTestSkipped('FreeDSx library not installed.');
        }

        $module = new LenientUserSecurityModelModule();

        $header = $this->createMock(MessageHeader::class);
        $header->method('hasAuthentication')->willReturn(false);
        $header->method('hasPrivacy')->willReturn(false);

        $securityParams = $this->createMock(UsmSecurityParameters::class);

        $message = $this->createMock(AbstractMessageV3::class);
        $message->method('getSecurityParameters')->willReturn($securityParams);
        $message->method('getMessageHeader')->willReturn($header);

        $result = $module->handleIncomingMessage($message, [
            'use_auth' => false,
            'use_priv' => false,
        ]);

        $this->assertSame($message, $result);
    }
}
