<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\PduException;

interface SnmpClientInterface
{
    /**
     * Get a single OID value using SNMPv1.
     *
     * @throws PduException
     */
    public function getV1(string $oid, string $community): string;

    /**
     * Batch get multiple OIDs using SNMPv1.
     *
     * @param array<string> $oids List of OIDs to fetch
     * @param string $community SNMP community string
     * @return array<string, string> Map of OID => raw SNMP value
     * @throws PduException
     */
    public function getV1Batch(array $oids, string $community): array;

    /**
     * Get a single OID value using SNMPv3.
     *
     * @throws PduException
     */
    public function getV3(
        string $oid,
        string $username,
        string $securityLevel,
        string $authProtocol,
        string $authPassphrase,
        string $privProtocol,
        string $privPassphrase,
    ): string;

    /**
     * Batch get multiple OIDs using SNMPv3.
     *
     * @param array<string> $oids List of OIDs to fetch
     * @param string $username SNMPv3 username
     * @param string $securityLevel Security level (noAuthNoPriv, authNoPriv, authPriv)
     * @param string $authProtocol Authentication protocol (MD5, SHA)
     * @param string $authPassphrase Authentication passphrase
     * @param string $privProtocol Privacy protocol (DES, AES)
     * @param string $privPassphrase Privacy passphrase
     * @return array<string, string> Map of OID => raw SNMP value
     * @throws PduException
     */
    public function getV3Batch(
        array $oids,
        string $username,
        string $securityLevel,
        string $authProtocol,
        string $authPassphrase,
        string $privProtocol,
        string $privPassphrase,
    ): array;

    /**
     * Get the host address.
     */
    public function getHost(): string;
}
