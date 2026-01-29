<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

use Sunfox\ApcPdu\Protocol\Snmp\SnmpV1Provider;
use Sunfox\ApcPdu\Protocol\Snmp\SnmpV3Provider;
use Sunfox\ApcPdu\Protocol\Ssh\SshProvider;

final class ApcPduFactory
{
    public static function snmpV1(
        string $host,
        string $community = 'public',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ): ApcPdu {
        return new ApcPdu(
            new SnmpV1Provider($host, $community, $outletsPerPdu, $timeout, $retries),
        );
    }

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
        return new ApcPdu(
            new SnmpV3Provider(
                $host,
                $username,
                $authPassphrase,
                $privPassphrase,
                $authProtocol,
                $privProtocol,
                $outletsPerPdu,
                $timeout,
                $retries,
            ),
        );
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
