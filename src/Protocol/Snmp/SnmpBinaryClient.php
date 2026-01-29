<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\SnmpBinaryNotFoundException;

/**
 * SNMP client using the snmpget binary for all operations.
 *
 * This client provides efficient batch operations using a single shell command
 * for multiple OIDs, but requires the net-snmp package to be installed.
 */
final class SnmpBinaryClient implements SnmpWritableClientInterface
{
    public function __construct(
        private string $host,
        private int $timeout = 1000000,
        private int $retries = 3,
    ) {
    }

    public static function isAvailable(): bool
    {
        $output = shell_exec('which snmpget 2>/dev/null');

        return $output !== null && trim($output) !== '';
    }

    public function getV1(string $oid, string $community): string
    {
        $result = $this->getV1Batch([$oid], $community);

        return $result[$oid];
    }

    /**
     * @inheritDoc
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
        $result = $this->getV3Batch(
            [$oid],
            $username,
            $securityLevel,
            $authProtocol,
            $authPassphrase,
            $privProtocol,
            $privPassphrase,
        );

        return $result[$oid];
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
            throw new PduException('SNMP GET failed: command execution failed');
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
            throw new PduException('SNMP GET failed: timeout');
        }

        if (str_contains($output, 'No Such Object') || str_contains($output, 'No Such Instance')) {
            throw new PduException('SNMP GET failed: object not found');
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
                    'SNMP GET failed: expected %d values, got %d',
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

    public function setV1(string $oid, string $type, string $value, string $community): void
    {
        $timeoutSeconds = (int) ceil($this->timeout / 1000000);

        $cmd = sprintf(
            'snmpset -v1 -c %s -t %d -r %d %s %s %s %s 2>&1',
            escapeshellarg($community),
            $timeoutSeconds,
            $this->retries,
            escapeshellarg($this->host),
            escapeshellarg($oid),
            escapeshellarg($type),
            escapeshellarg($value),
        );

        $this->executeSetCommand($cmd, $oid);
    }

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
    ): void {
        $timeoutSeconds = (int) ceil($this->timeout / 1000000);

        $cmd = sprintf(
            'snmpset -v3 -l %s -u %s -a %s -A %s -x %s -X %s -t %d -r %d %s %s %s %s 2>&1',
            escapeshellarg($securityLevel),
            escapeshellarg($username),
            escapeshellarg($authProtocol),
            escapeshellarg($authPassphrase),
            escapeshellarg($privProtocol),
            escapeshellarg($privPassphrase),
            $timeoutSeconds,
            $this->retries,
            escapeshellarg($this->host),
            escapeshellarg($oid),
            escapeshellarg($type),
            escapeshellarg($value),
        );

        $this->executeSetCommand($cmd, $oid);
    }

    /**
     * Execute SNMP SET command.
     *
     * @throws PduException
     * @throws SnmpBinaryNotFoundException
     */
    private function executeSetCommand(string $cmd, string $oid): void
    {
        $output = shell_exec($cmd);

        if ($output === null || $output === false) {
            throw new PduException("SNMP SET failed for OID: {$oid} - command execution failed");
        }

        $output = trim($output);

        // Check if snmpset binary is not found
        if (
            str_contains($output, 'snmpset: not found')
            || str_contains($output, 'snmpset: command not found')
            || str_contains($output, 'not found')
            && str_contains($output, 'snmpset')
        ) {
            throw new SnmpBinaryNotFoundException('snmpset');
        }

        if (str_contains($output, 'Timeout') || str_contains($output, 'No Response')) {
            throw new PduException("SNMP SET failed for OID: {$oid} - timeout");
        }

        if (str_contains($output, 'Error') || str_contains($output, 'failed')) {
            throw new PduException("SNMP SET failed for OID: {$oid} - {$output}");
        }
    }
}
