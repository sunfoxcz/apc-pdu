<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

use Sunfox\ApcPdu\Protocol\Snmp\SnmpBinaryClient;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpFreeDsxClient;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpNativeClient;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpV1Provider;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpV3Provider;
use Sunfox\ApcPdu\Protocol\Ssh\SshProvider;
use Sunfox\ApcPdu\NoSnmpClientAvailableException;

final class ApcPduFactory
{
    /**
     * Create an SNMPv1 connection using PHP's native SNMP functions.
     *
     * This is portable and works without external binary dependencies,
     * but batch operations loop over OIDs calling single-get (slower).
     */
    public static function snmpV1Native(
        string $host,
        string $community = 'public',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ): ApcPdu {
        $client = new SnmpNativeClient($host, $timeout, $retries);

        return new ApcPdu(
            new SnmpV1Provider($client, $community, $outletsPerPdu),
        );
    }

    /**
     * Create an SNMPv1 connection using the snmpget binary.
     *
     * This provides efficient batch operations using a single shell command
     * for multiple OIDs, but requires the net-snmp package to be installed.
     */
    public static function snmpV1Binary(
        string $host,
        string $community = 'public',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ): ApcPdu {
        $client = new SnmpBinaryClient($host, $timeout, $retries);

        return new ApcPdu(
            new SnmpV1Provider($client, $community, $outletsPerPdu),
        );
    }

    /**
     * Create an SNMPv1 connection using the FreeDSx SNMP library.
     *
     * This provides efficient batch operations and does not require
     * PHP's native SNMP extension or the net-snmp package.
     * Requires: composer require freedsx/snmp
     *
     * @throws SnmpFreeDsxNotFoundException
     */
    public static function snmpV1FreeDsx(
        string $host,
        string $community = 'public',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ): ApcPdu {
        $client = new SnmpFreeDsxClient($host, $timeout, $retries);

        return new ApcPdu(
            new SnmpV1Provider($client, $community, $outletsPerPdu),
        );
    }

    /**
     * Create SNMPv1 connection with automatic client discovery.
     *
     * Automatically selects the best available SNMP client in priority order:
     * 1. SnmpBinaryClient (most efficient batch operations)
     * 2. SnmpFreeDsxClient (efficient batch operations, no binary dependency)
     * 3. SnmpNativeClient (fallback, slower batch operations)
     *
     * @throws NoSnmpClientAvailableException
     */
    public static function snmpV1(
        string $host,
        string $community = 'public',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ): ApcPdu {
        if (SnmpBinaryClient::isAvailable()) {
            return self::snmpV1Binary($host, $community, $outletsPerPdu, $timeout, $retries);
        }
        if (SnmpFreeDsxClient::isAvailable()) {
            return self::snmpV1FreeDsx($host, $community, $outletsPerPdu, $timeout, $retries);
        }
        if (SnmpNativeClient::isAvailable()) {
            return self::snmpV1Native($host, $community, $outletsPerPdu, $timeout, $retries);
        }

        throw new NoSnmpClientAvailableException();
    }

    /**
     * Create an SNMPv3 connection using PHP's native SNMP functions.
     *
     * This is portable and works without external binary dependencies,
     * but batch operations loop over OIDs calling single-get (slower).
     */
    public static function snmpV3Native(
        string $host,
        string $username,
        string $authPassphrase,
        string $privPassphrase = '',
        string $authProtocol = 'SHA',
        string $privProtocol = 'AES',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ): ApcPdu {
        $client = new SnmpNativeClient($host, $timeout, $retries);

        return new ApcPdu(
            new SnmpV3Provider(
                $client,
                $username,
                $authPassphrase,
                $privPassphrase,
                $authProtocol,
                $privProtocol,
                $outletsPerPdu,
            ),
        );
    }

    /**
     * Create an SNMPv3 connection using the snmpget binary.
     *
     * This provides efficient batch operations using a single shell command
     * for multiple OIDs, but requires the net-snmp package to be installed.
     */
    public static function snmpV3Binary(
        string $host,
        string $username,
        string $authPassphrase,
        string $privPassphrase = '',
        string $authProtocol = 'SHA',
        string $privProtocol = 'AES',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ): ApcPdu {
        $client = new SnmpBinaryClient($host, $timeout, $retries);

        return new ApcPdu(
            new SnmpV3Provider(
                $client,
                $username,
                $authPassphrase,
                $privPassphrase,
                $authProtocol,
                $privProtocol,
                $outletsPerPdu,
            ),
        );
    }

    /**
     * Create an SNMPv3 connection using the FreeDSx SNMP library.
     *
     * This provides efficient batch operations and does not require
     * PHP's native SNMP extension or the net-snmp package.
     * Requires: composer require freedsx/snmp
     *
     * @throws SnmpFreeDsxNotFoundException
     */
    public static function snmpV3FreeDsx(
        string $host,
        string $username,
        string $authPassphrase,
        string $privPassphrase = '',
        string $authProtocol = 'SHA',
        string $privProtocol = 'AES',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ): ApcPdu {
        $client = new SnmpFreeDsxClient($host, $timeout, $retries);

        return new ApcPdu(
            new SnmpV3Provider(
                $client,
                $username,
                $authPassphrase,
                $privPassphrase,
                $authProtocol,
                $privProtocol,
                $outletsPerPdu,
            ),
        );
    }

    /**
     * Create SNMPv3 connection with automatic client discovery.
     *
     * Automatically selects the best available SNMP client in priority order:
     * 1. SnmpBinaryClient (most efficient batch operations)
     * 2. SnmpFreeDsxClient (efficient batch operations, no binary dependency)
     * 3. SnmpNativeClient (fallback, slower batch operations)
     *
     * @throws NoSnmpClientAvailableException
     */
    public static function snmpV3(
        string $host,
        string $username,
        string $authPassphrase,
        string $privPassphrase = '',
        string $authProtocol = 'SHA',
        string $privProtocol = 'AES',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ): ApcPdu {
        if (SnmpBinaryClient::isAvailable()) {
            return self::snmpV3Binary(
                $host,
                $username,
                $authPassphrase,
                $privPassphrase,
                $authProtocol,
                $privProtocol,
                $outletsPerPdu,
                $timeout,
                $retries,
            );
        }
        if (SnmpFreeDsxClient::isAvailable()) {
            return self::snmpV3FreeDsx(
                $host,
                $username,
                $authPassphrase,
                $privPassphrase,
                $authProtocol,
                $privProtocol,
                $outletsPerPdu,
                $timeout,
                $retries,
            );
        }
        if (SnmpNativeClient::isAvailable()) {
            return self::snmpV3Native(
                $host,
                $username,
                $authPassphrase,
                $privPassphrase,
                $authProtocol,
                $privProtocol,
                $outletsPerPdu,
                $timeout,
                $retries,
            );
        }

        throw new NoSnmpClientAvailableException();
    }

    public static function ssh(
        string $host,
        string $username,
        string $password,
        int $outletsPerPdu = 24,
        int $port = 22,
    ): ApcPdu {
        return new ApcPdu(
            new SshProvider($host, $username, $password, $outletsPerPdu, $port),
        );
    }
}
