<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\PduOutletMetric;
use Sunfox\ApcPdu\Protocol\ProtocolProviderInterface;

final class SnmpV1Provider implements ProtocolProviderInterface
{
    private SnmpClient $client;
    private ApcAp8xxxOidMap $oidMap;
    private SnmpResponseParser $parser;

    public function __construct(
        string $host,
        private string $community = 'public',
        private int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3,
    ) {
        $this->client = new SnmpClient($host, $timeout, $retries);
        $this->oidMap = new ApcAp8xxxOidMap();
        $this->parser = new SnmpResponseParser();
    }

    public function getDeviceMetric(DeviceMetric $metric, int $pduIndex): float
    {
        $oid = $this->oidMap->deviceOid($metric, $pduIndex);
        $raw = $this->client->getV1($oid, $this->community);
        $value = $this->parser->parseNumeric($raw);

        return $value / $this->oidMap->getDeviceDivisor($metric);
    }

    public function getOutletMetric(PduOutletMetric $metric, int $pduIndex, int $outletNumber): float|string
    {
        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);
        $oid = $this->oidMap->outletOid($metric, $snmpIndex);
        $raw = $this->client->getV1($oid, $this->community);

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
}
