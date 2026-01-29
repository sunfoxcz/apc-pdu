<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;
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

    public function getDeviceMetric(DeviceMetric $metric, int $pduIndex): float|int|string
    {
        $oid = $this->oidMap->deviceOid($metric, $pduIndex);
        $raw = $this->client->getV1($oid, $this->community);

        if ($metric->isString()) {
            return $this->parser->parseString($raw);
        }

        $value = $this->parser->parseNumeric($raw);
        $divisor = $this->oidMap->getDeviceDivisor($metric);

        if ($metric->isInteger()) {
            return (int) $value;
        }

        return $value / $divisor;
    }

    /**
     * @inheritDoc
     */
    public function getDeviceMetricsBatch(int $pduIndex): array
    {
        $oids = [];
        $metrics = [];

        foreach (DeviceMetric::cases() as $metric) {
            $oid = $this->oidMap->deviceOid($metric, $pduIndex);
            $oids[] = $oid;
            $metrics[$oid] = $metric;
        }

        $rawResults = $this->client->getV1Batch($oids, $this->community);

        $results = [];
        foreach ($metrics as $oid => $metric) {
            $raw = $rawResults[$oid];

            if ($metric->isString()) {
                $results[$metric->value] = $this->parser->parseStringBatch($raw);
                continue;
            }

            $value = $this->parser->parseNumericBatch($raw);
            $divisor = $this->oidMap->getDeviceDivisor($metric);

            if ($metric->isInteger()) {
                $results[$metric->value] = (int) $value;
            } else {
                $results[$metric->value] = $value / $divisor;
            }
        }

        return $results;
    }

    public function getOutletMetric(PduOutletMetric $metric, int $pduIndex, int $outletNumber): float|int|string
    {
        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);
        $oid = $this->oidMap->outletOid($metric, $snmpIndex);
        $raw = $this->client->getV1($oid, $this->community);

        if ($metric->isString()) {
            return $this->parser->parseString($raw);
        }

        $value = $this->parser->parseNumeric($raw);
        $divisor = $this->oidMap->getOutletDivisor($metric);

        if ($metric->isInteger()) {
            return (int) $value;
        }

        return $value / $divisor;
    }

    /**
     * @inheritDoc
     */
    public function getOutletMetricsBatch(int $pduIndex, int $outletNumber): array
    {
        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);

        $oids = [];
        $metrics = [];

        foreach (OutletMetric::cases() as $metric) {
            $oid = $this->oidMap->outletOid($metric, $snmpIndex);
            $oids[] = $oid;
            $metrics[$oid] = $metric;
        }

        $rawResults = $this->client->getV1Batch($oids, $this->community);

        $results = [];
        foreach ($metrics as $oid => $metric) {
            $raw = $rawResults[$oid];

            if ($metric->isString()) {
                $results[$metric->value] = $this->parser->parseStringBatch($raw);
                continue;
            }

            $value = $this->parser->parseNumericBatch($raw);
            $divisor = $this->oidMap->getOutletDivisor($metric);

            if ($metric->isInteger()) {
                $results[$metric->value] = (int) $value;
            } else {
                $results[$metric->value] = $value / $divisor;
            }
        }

        return $results;
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
