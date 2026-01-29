<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\SnmpBinaryNotFoundException;

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

    /**
     * Batch get multiple OIDs using SNMPv1.
     *
     * Uses shell execution of snmpget binary to fetch multiple OIDs in a single request.
     *
     * @param array<string> $oids List of OIDs to fetch
     * @param string $community SNMP community string
     * @return array<string, string> Map of OID => raw SNMP value
     * @throws PduException
     */
    public function getV1Batch(array $oids, string $community): array
    {
        if ($oids === []) {
            return [];
        }

        $timeoutSeconds = (int) ceil($this->timeout / 1000000);
        $oidsEscaped = array_map('escapeshellarg', $oids);

        $cmd = sprintf(
            'snmpget -v1 -c %s -t %d -r %d -Oqv %s %s 2>&1',
            escapeshellarg($community),
            $timeoutSeconds,
            $this->retries,
            escapeshellarg($this->host),
            implode(' ', $oidsEscaped),
        );

        $output = $this->executeCommand($cmd);

        return $this->parseBatchOutput($oids, $output);
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
     * Batch get multiple OIDs using SNMPv3.
     *
     * Uses shell execution of snmpget binary to fetch multiple OIDs in a single request.
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
    ): array {
        if ($oids === []) {
            return [];
        }

        $timeoutSeconds = (int) ceil($this->timeout / 1000000);
        $oidsEscaped = array_map('escapeshellarg', $oids);

        $cmd = sprintf(
            'snmpget -v3 -l %s -u %s -a %s -A %s -x %s -X %s -t %d -r %d -Oqv %s %s 2>&1',
            escapeshellarg($securityLevel),
            escapeshellarg($username),
            escapeshellarg($authProtocol),
            escapeshellarg($authPassphrase),
            escapeshellarg($privProtocol),
            escapeshellarg($privPassphrase),
            $timeoutSeconds,
            $this->retries,
            escapeshellarg($this->host),
            implode(' ', $oidsEscaped),
        );

        $output = $this->executeCommand($cmd);

        return $this->parseBatchOutput($oids, $output);
    }

    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Execute shell command and return output.
     *
     * @throws PduException
     * @throws SnmpBinaryNotFoundException
     */
    private function executeCommand(string $cmd): string
    {
        $output = shell_exec($cmd);

        if ($output === null || $output === false) {
            throw new PduException('SNMP batch GET failed: command execution failed');
        }

        $output = trim($output);

        // Check if snmpget binary is not found
        if (
            str_contains($output, 'snmpget: not found')
            || str_contains($output, 'snmpget: command not found')
            || str_contains($output, 'not found')
            && str_contains($output, 'snmpget')
        ) {
            throw new SnmpBinaryNotFoundException('snmpget');
        }

        if (str_contains($output, 'Timeout') || str_contains($output, 'No Response')) {
            throw new PduException('SNMP batch GET failed: timeout');
        }

        if (str_contains($output, 'No Such Object') || str_contains($output, 'No Such Instance')) {
            throw new PduException('SNMP batch GET failed: object not found');
        }

        return $output;
    }

    /**
     * Parse batch snmpget output and map to OIDs.
     *
     * @param array<string> $oids List of OIDs in order
     * @param string $output Raw command output
     * @return array<string, string> Map of OID => raw SNMP value
     * @throws PduException
     */
    private function parseBatchOutput(array $oids, string $output): array
    {
        $lines = explode("\n", $output);
        $results = [];

        if (count($lines) !== count($oids)) {
            throw new PduException(
                sprintf(
                    'SNMP batch GET failed: expected %d values, got %d',
                    count($oids),
                    count($lines),
                ),
            );
        }

        foreach ($oids as $i => $oid) {
            $results[$oid] = $lines[$i];
        }

        return $results;
    }
}
