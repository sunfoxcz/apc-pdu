<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Exception thrown when FreeDSx SNMP library is not installed
 */
class SnmpFreeDsxNotFoundException extends PduException
{
    public function __construct()
    {
        parent::__construct(
            'FreeDSx SNMP library not found. Install it with: composer require freedsx/snmp'
        );
    }
}
