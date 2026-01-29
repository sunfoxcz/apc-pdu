<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\PduOutletMetric;
use Sunfox\ApcPdu\Protocol\ProtocolProviderInterface;

final class SnmpV3Provider implements ProtocolProviderInterface
{
    private ApcAp8xxxOidMap $oidMap;
    private SnmpResponseParser $parser;
    private string $securityLevel;

    public function __construct(
        private SnmpClientInterface $client,
        private string $username,
        private string $authPassphrase,
        private string $privPassphrase = '',
        private string $authProtocol = 'SHA',
        private string $privProtocol = 'AES',
        private int $outletsPerPdu = 24,
    ) {
        $this->oidMap = new ApcAp8xxxOidMap();
        $this->parser = new SnmpResponseParser();
        $this->securityLevel = $this->determineSecurityLevel();
    }

    public function getDeviceMetric(DeviceMetric $metric, int $pduIndex): float|int|string
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

        $rawResults = $this->client->getV3Batch(
            $oids,
            $this->username,
            $this->securityLevel,
            $this->authProtocol,
            $this->authPassphrase,
            $this->privProtocol,
            $this->privPassphrase,
        );

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

        $rawResults = $this->client->getV3Batch(
            $oids,
            $this->username,
            $this->securityLevel,
            $this->authProtocol,
            $this->authPassphrase,
            $this->privProtocol,
            $this->privPassphrase,
        );

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
