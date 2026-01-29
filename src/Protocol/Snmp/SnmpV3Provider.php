<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\PduOutletMetric;
use Sunfox\ApcPdu\Protocol\ProtocolProviderInterface;

final class SnmpV3Provider implements ProtocolProviderInterface
{
    private SnmpClient $client;
    private ApcAp8xxxOidMap $oidMap;
    private SnmpResponseParser $parser;
    private string $securityLevel;

    public function __construct(
        string $host,
        private string $username,
        private string $authPassphrase,
        private string $privPassphrase = '',
        private string $authProtocol = 'SHA',
        private string $privProtocol = 'AES',
        private int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ) {
        $this->client = new SnmpClient($host, $timeout, $retries);
        $this->oidMap = new ApcAp8xxxOidMap();
        $this->parser = new SnmpResponseParser();
        $this->securityLevel = $this->determineSecurityLevel();
    }

    public function getDeviceMetric(DeviceMetric $metric, int $pduIndex): float
    {
        $oid = $this->oidMap->deviceOid($metric, $pduIndex);
        $raw = $this->client->getV3(
            $oid,
            $this->username,
            $this->securityLevel,
            $this->authProtocol,
            $this->authPassphrase,
            $this->privProtocol,
            $this->privPassphrase,
        );
        $value = $this->parser->parseNumeric($raw);

        return $value / $this->oidMap->getDeviceDivisor($metric);
    }

    public function getOutletMetric(PduOutletMetric $metric, int $pduIndex, int $outletNumber): float|string
    {
        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);
        $oid = $this->oidMap->outletOid($metric, $snmpIndex);
        $raw = $this->client->getV3(
            $oid,
            $this->username,
            $this->securityLevel,
            $this->authProtocol,
            $this->authPassphrase,
            $this->privProtocol,
            $this->privPassphrase,
        );

        if ($metric->isString()) {
            return $this->parser->parseString($raw);
        }

        return $this->parser->parseNumeric($raw) / $this->oidMap->getOutletDivisor($metric);
    }

    public function getHost(): string
    {
        return $this->client->getHost();
    }

    public function getOutletsPerPdu(): int
    {
        return $this->outletsPerPdu;
    }

    private function determineSecurityLevel(): string
    {
        if ($this->privPassphrase !== '') {
            return 'authPriv';
        }

        if ($this->authPassphrase !== '') {
            return 'authNoPriv';
        }

        return 'noAuthNoPriv';
    }
}
