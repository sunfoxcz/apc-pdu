<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

class NoSnmpClientAvailableException extends PduException
{
    public function __construct()
    {
        parent::__construct(
            'No SNMP client available. Install one of: ' .
            'net-snmp package (snmpget binary), ' .
            'freedsx/snmp composer package, ' .
            'or PHP snmp extension.'
        );
    }
}
