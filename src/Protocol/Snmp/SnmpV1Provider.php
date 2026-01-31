<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\PduOutletMetric;
use Sunfox\ApcPdu\Protocol\WritableProtocolProviderInterface;

final class SnmpV1Provider implements WritableProtocolProviderInterface
{
    private ApcAp8xxxOidMap $oidMap;
    private SnmpResponseParser $parser;

    public function __construct(
        private SnmpClientInterface $client,
        private string $community = 'public',
        private int $outletsPerPdu = 24,
    ) {
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
        $oid = $this->oidMap->outletOid($metric, $snmpIndex, $pduIndex);
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
            $oid = $this->oidMap->outletOid($metric, $snmpIndex, $pduIndex);
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

    public function setOutletName(int $pduIndex, int $outletNumber, string $name): void
    {
        if (!$this->isWritable()) {
            throw new \Sunfox\ApcPdu\PduException('SNMP client does not support write operations');
        }

        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);
        $oid = $this->oidMap->outletNameConfigOid($snmpIndex);

        /** @var SnmpWritableClientInterface $client */
        $client = $this->client;
        $client->setV1($oid, 's', $name, $this->community);
    }

    public function setOutletState(
        int $pduIndex,
        int $outletNumber,
        \Sunfox\ApcPdu\OutletCommand $command,
    ): void {
        if (!$this->isWritable()) {
            throw new \Sunfox\ApcPdu\PduException('SNMP client does not support write operations');
        }

        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);
        $oid = $this->oidMap->outletStateControlOid($snmpIndex);

        /** @var SnmpWritableClientInterface $client */
        $client = $this->client;
        $client->setV1($oid, 'i', (string) $command->value, $this->community);
    }

    public function setOutletExternalLink(int $pduIndex, int $outletNumber, string $url): void
    {
        if (!$this->isWritable()) {
            throw new \Sunfox\ApcPdu\PduException('SNMP client does not support write operations');
        }

        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);
        $oid = $this->oidMap->outletExternalLinkConfigOid($snmpIndex);

        /** @var SnmpWritableClientInterface $client */
        $client = $this->client;
        $client->setV1($oid, 's', $url, $this->community);
    }

    public function setOutletLowLoadThreshold(int $pduIndex, int $outletNumber, int $threshold): void
    {
        if (!$this->isWritable()) {
            throw new \Sunfox\ApcPdu\PduException('SNMP client does not support write operations');
        }

        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);
        $oid = $this->oidMap->outletLowLoadThresholdConfigOid($snmpIndex);

        /** @var SnmpWritableClientInterface $client */
        $client = $this->client;
        $client->setV1($oid, 'i', (string) $threshold, $this->community);
    }

    public function setOutletNearOverloadThreshold(int $pduIndex, int $outletNumber, int $threshold): void
    {
        if (!$this->isWritable()) {
            throw new \Sunfox\ApcPdu\PduException('SNMP client does not support write operations');
        }

        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);
        $oid = $this->oidMap->outletNearOverloadThresholdConfigOid($snmpIndex);

        /** @var SnmpWritableClientInterface $client */
        $client = $this->client;
        $client->setV1($oid, 'i', (string) $threshold, $this->community);
    }

    public function setOutletOverloadThreshold(int $pduIndex, int $outletNumber, int $threshold): void
    {
        if (!$this->isWritable()) {
            throw new \Sunfox\ApcPdu\PduException('SNMP client does not support write operations');
        }

        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);
        $oid = $this->oidMap->outletOverloadThresholdConfigOid($snmpIndex);

        /** @var SnmpWritableClientInterface $client */
        $client = $this->client;
        $client->setV1($oid, 'i', (string) $threshold, $this->community);
    }

    public function resetDevicePeakPower(int $pduIndex): void
    {
        $this->executeReset($this->oidMap->devicePeakPowerResetOid($pduIndex));
    }

    public function resetDeviceEnergy(int $pduIndex): void
    {
        $this->executeReset($this->oidMap->deviceEnergyResetOid($pduIndex));
    }

    public function resetOutletsEnergy(int $pduIndex): void
    {
        $this->executeReset($this->oidMap->outletsEnergyResetOid($pduIndex));
    }

    public function resetOutletsPeakPower(int $pduIndex): void
    {
        $this->executeReset($this->oidMap->outletsPeakPowerResetOid($pduIndex));
    }

    public function isWritable(): bool
    {
        return $this->client instanceof SnmpWritableClientInterface;
    }

    private function executeReset(string $oid): void
    {
        if (!$this->isWritable()) {
            throw new \Sunfox\ApcPdu\PduException('SNMP client does not support write operations');
        }

        /** @var SnmpWritableClientInterface $client */
        $client = $this->client;

        try {
            $client->setV1($oid, 'i', '2', $this->community);
        } catch (\Sunfox\ApcPdu\PduException $e) {
            // APC PDUs often drop the connection after processing reset commands
            // but the operation succeeds. Ignore connection-related errors.
            if (!str_contains($e->getMessage(), 'No message received from host')) {
                throw $e;
            }
        }
    }
}
