<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\PduException;

final class SnmpClient
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

    public function getHost(): string
    {
        return $this->host;
    }
}
