<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Integration;

use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\ApcPduFactory;

/**
 * Integration tests for FreeDSx SNMP client.
 *
 * @group integration
 * @group freedsx
 */
class ApcPduFreeDsxIntegrationTest extends AbstractSnmpIntegrationTestCase
{
    protected function createPdu(
        string $host,
        string $user,
        string $authPass,
        string $privPass,
    ): ApcPdu {
        return ApcPduFactory::snmpV3FreeDsx($host, $user, $authPass, $privPass);
    }
}
