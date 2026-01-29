<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\PduException;

/**
 * SNMP client using PHP's native snmpget() and snmp3_get() functions.
 *
 * This client is portable and works without external binary dependencies,
 * but batch operations loop over OIDs calling single-get (slower).
 */
final class SnmpNativeClient implements SnmpClientInterface
{
    public function __construct(
        private string $host,
        private int $timeout = 1000000,
        private int $retries = 3,
    ) {
    }

    public function getV1(string $oid, string $community): string
    {
        $result = @snmpget($this->host, $community, $oid, $this->timeout, $this->retries);

        if ($result === false) {
            $error = error_get_last();
            throw new PduException("SNMP GET failed for OID: {$oid}" . ($error ? " - {$error['message']}" : ''));
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getV1Batch(array $oids, string $community): array
    {
        if ($oids === []) {
            return [];
        }

        $results = [];
        foreach ($oids as $oid) {
            $results[$oid] = $this->getV1($oid, $community);
        }

        return $results;
    }

    public function getV3(
        string $oid,
        string $username,
        string $securityLevel,
        string $authProtocol,
        string $authPassphrase,
        string $privProtocol,
        string $privPassphrase,
    ): string {
        $result = @snmp3_get(
            $this->host,
            $username,
            $securityLevel,
            $authProtocol,
            $authPassphrase,
            $privProtocol,
            $privPassphrase,
            $oid,
            $this->timeout,
            $this->retries,
        );

        if ($result === false) {
            $error = error_get_last();
            throw new PduException("SNMP GET failed for OID: {$oid}" . ($error ? " - {$error['message']}" : ''));
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getV3Batch(
        array $oids,
        string $username,
        string $securityLevel,
        string $authProtocol,
        string $authPassphrase,
        string $privProtocol,
        string $privPassphrase,
    ): array {
        if ($oids === []) {
            return [];
        }

        $results = [];
        foreach ($oids as $oid) {
            $results[$oid] = $this->getV3(
                $oid,
                $username,
                $securityLevel,
                $authProtocol,
                $authPassphrase,
                $privProtocol,
                $privPassphrase,
            );
        }

        return $results;
    }

    public function getHost(): string
    {
        return $this->host;
    }
}
