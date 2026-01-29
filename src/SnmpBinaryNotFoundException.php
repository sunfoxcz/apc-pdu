<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Exception thrown when the snmpget binary is not found on the system.
 *
 * This exception indicates that the net-snmp package (or equivalent) needs to be installed.
 * On Debian/Ubuntu: apt-get install snmp
 * On RHEL/CentOS: yum install net-snmp-utils
 * On Alpine: apk add net-snmp-tools
 */
class SnmpBinaryNotFoundException extends PduException
{
    public function __construct(string $binary = 'snmpget')
    {
        parent::__construct(
            "SNMP binary '{$binary}' not found. Please install net-snmp package " .
            '(apt-get install snmp, yum install net-snmp-utils, or apk add net-snmp-tools).',
        );
    }
}
