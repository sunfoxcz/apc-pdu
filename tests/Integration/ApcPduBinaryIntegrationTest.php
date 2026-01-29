<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Integration;

use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\ApcPduFactory;

/**
 * Integration tests for Binary SNMP client.
 *
 * @group integration
 * @group binary
 */
class ApcPduBinaryIntegrationTest extends AbstractSnmpIntegrationTest
{
    protected function createPdu(
        string $host,
        string $user,
        string $authPass,
        string $privPass,
    ): ApcPdu {
        return ApcPduFactory::snmpV3Binary($host, $user, $authPass, $privPass);
    }
}
