<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\PduException;

interface SnmpWritableClientInterface extends SnmpClientInterface
{
    /**
     * Set a single OID value using SNMPv1.
     *
     * @param string $oid The OID to set
     * @param string $type SNMP type (s = string, i = integer, etc.)
     * @param string $value The value to set
     * @param string $community SNMP community string
     * @throws PduException
     */
    public function setV1(string $oid, string $type, string $value, string $community): void;

    /**
     * Set a single OID value using SNMPv3.
     *
     * @param string $oid The OID to set
     * @param string $type SNMP type (s = string, i = integer, etc.)
     * @param string $value The value to set
     * @param string $username SNMPv3 username
     * @param string $securityLevel Security level (noAuthNoPriv, authNoPriv, authPriv)
     * @param string $authProtocol Authentication protocol (MD5, SHA)
     * @param string $authPassphrase Authentication passphrase
     * @param string $privProtocol Privacy protocol (DES, AES)
     * @param string $privPassphrase Privacy passphrase
     * @throws PduException
     */
    public function setV3(
        string $oid,
        string $type,
        string $value,
        string $username,
        string $securityLevel,
        string $authProtocol,
        string $authPassphrase,
        string $privProtocol,
        string $privPassphrase,
    ): void;
}
