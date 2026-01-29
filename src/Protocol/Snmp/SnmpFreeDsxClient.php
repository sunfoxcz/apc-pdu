<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use FreeDSx\Snmp\Protocol\ClientProtocolHandler;
use FreeDSx\Snmp\SnmpClient;
use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpFreeDsxClient\LenientSecurityModelModuleFactory;
use Sunfox\ApcPdu\SnmpFreeDsxNotFoundException;
use Throwable;

/**
 * SNMP client using the FreeDSx SNMP library.
 *
 * This client requires the freedsx/snmp package to be installed.
 * Install it with: composer require freedsx/snmp
 */
final class SnmpFreeDsxClient implements SnmpClientInterface
{
    /**
     * @var array<string, string>
     */
    private const AUTH_PROTOCOL_MAP = [
        'SHA' => 'sha1',
        'MD5' => 'md5',
    ];

    /**
     * @var array<string, string>
     */
    private const PRIV_PROTOCOL_MAP = [
        'AES' => 'aes128',
        'DES' => 'des',
    ];

    private int $timeoutSeconds;

    /**
     * @throws SnmpFreeDsxNotFoundException
     */
    public function __construct(
        private string $host,
        int $timeout = 1000000,
        private int $retries = 3,
    ) {
        if (!class_exists(SnmpClient::class)) {
            throw new SnmpFreeDsxNotFoundException();
        }

        // Convert microseconds to seconds with minimum of 1
        $this->timeoutSeconds = max(1, (int) ($timeout / 1000000));
    }

    public static function isAvailable(): bool
    {
        return class_exists(SnmpClient::class);
    }

    public function getV1(string $oid, string $community): string
    {
        $client = new SnmpClient([
            'host' => $this->host,
            'version' => 1,
            'community' => $community,
            'timeout_read' => $this->timeoutSeconds,
            'retries' => $this->retries,
        ]);

        try {
            $value = $client->getValue($oid);
            if ($value === null) {
                throw new PduException("SNMP GET failed for OID: {$oid} - null value returned");
            }
            return (string) $value;
        } catch (PduException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new PduException("SNMP GET failed for OID: {$oid} - {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getV1Batch(array $oids, string $community): array
    {
        if ($oids === []) {
            return [];
        }

        $client = new SnmpClient([
            'host' => $this->host,
            'version' => 1,
            'community' => $community,
            'timeout_read' => $this->timeoutSeconds,
            'retries' => $this->retries,
        ]);

        try {
            $response = $client->get(...$oids);
            $results = [];
            foreach ($response as $oidObj) {
                // Normalize OID to have leading dot for consistency
                $oid = $oidObj->getOid();
                if (!str_starts_with($oid, '.')) {
                    $oid = '.' . $oid;
                }
                $results[$oid] = (string) $oidObj->getValue();
            }
            return $results;
        } catch (Throwable $e) {
            throw new PduException("SNMP batch GET failed - {$e->getMessage()}", 0, $e);
        }
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
        $client = $this->createV3Client(
            $username,
            $securityLevel,
            $authProtocol,
            $authPassphrase,
            $privProtocol,
            $privPassphrase,
        );

        try {
            $value = $client->getValue($oid);
            if ($value === null) {
                throw new PduException("SNMP GET failed for OID: {$oid} - null value returned");
            }
            return (string) $value;
        } catch (PduException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new PduException("SNMP GET failed for OID: {$oid} - {$e->getMessage()}", 0, $e);
        }
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

        $client = $this->createV3Client(
            $username,
            $securityLevel,
            $authProtocol,
            $authPassphrase,
            $privProtocol,
            $privPassphrase,
        );

        try {
            $response = $client->get(...$oids);
            $results = [];
            foreach ($response as $oidObj) {
                // Normalize OID to have leading dot for consistency
                $oid = $oidObj->getOid();
                if (!str_starts_with($oid, '.')) {
                    $oid = '.' . $oid;
                }
                $results[$oid] = (string) $oidObj->getValue();
            }
            return $results;
        } catch (Throwable $e) {
            throw new PduException("SNMP batch GET failed - {$e->getMessage()}", 0, $e);
        }
    }

    public function getHost(): string
    {
        return $this->host;
    }

    private function createV3Client(
        string $username,
        string $securityLevel,
        string $authProtocol,
        string $authPassphrase,
        string $privProtocol,
        string $privPassphrase,
    ): SnmpClient {
        $useAuth = $securityLevel === 'authPriv' || $securityLevel === 'authNoPriv';
        $usePriv = $securityLevel === 'authPriv';

        $options = [
            'host' => $this->host,
            'version' => 3,
            'user' => $username,
            'use_auth' => $useAuth,
            'use_priv' => $usePriv,
            'timeout_read' => $this->timeoutSeconds,
            'retries' => $this->retries,
        ];

        if ($useAuth) {
            $options['auth_mech'] = self::AUTH_PROTOCOL_MAP[$authProtocol] ?? 'sha1';
            $options['auth_pwd'] = $authPassphrase;
        }

        if ($usePriv) {
            $options['priv_mech'] = self::PRIV_PROTOCOL_MAP[$privProtocol] ?? 'aes128';
            $options['priv_pwd'] = $privPassphrase;
        }

        // Use custom protocol handler with lenient security model
        // Some APC PDUs don't set auth/priv flags in response headers properly
        $options['_protocol_handler'] = new ClientProtocolHandler(
            $options,
            null,
            null,
            null,
            new LenientSecurityModelModuleFactory(),
        );

        return new SnmpClient($options);
    }
}
