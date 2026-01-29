<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Integration;

use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\ApcPduFactory;

/**
 * Integration tests for Native SNMP client.
 *
 * @group integration
 * @group native
 */
class ApcPduNativeIntegrationTest extends AbstractSnmpIntegrationTestCase
{
    protected function createPdu(
        string $host,
        string $user,
        string $authPass,
        string $privPass,
    ): ApcPdu {
        return ApcPduFactory::snmpV3Native($host, $user, $authPass, $privPass);
    }
}
