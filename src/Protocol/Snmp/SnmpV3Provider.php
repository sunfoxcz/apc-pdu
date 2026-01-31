<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\PduOutletMetric;
use Sunfox\ApcPdu\Protocol\WritableProtocolProviderInterface;

final class SnmpV3Provider implements WritableProtocolProviderInterface
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

    public function setOutletName(int $pduIndex, int $outletNumber, string $name): void
    {
        if (!$this->isWritable()) {
            throw new \Sunfox\ApcPdu\PduException('SNMP client does not support write operations');
        }

        $snmpIndex = $this->oidMap->outletToSnmpIndex($pduIndex, $outletNumber, $this->outletsPerPdu);
        $oid = $this->oidMap->outletNameConfigOid($snmpIndex);

        /** @var SnmpWritableClientInterface $client */
        $client = $this->client;
        $client->setV3(
            $oid,
            's',
            $name,
            $this->username,
            $this->securityLevel,
            $this->authProtocol,
            $this->authPassphrase,
            $this->privProtocol,
            $this->privPassphrase,
        );
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
        $client->setV3(
            $oid,
            'i',
            (string) $command->value,
            $this->username,
            $this->securityLevel,
            $this->authProtocol,
            $this->authPassphrase,
            $this->privProtocol,
            $this->privPassphrase,
        );
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
        $client->setV3(
            $oid,
            's',
            $url,
            $this->username,
            $this->securityLevel,
            $this->authProtocol,
            $this->authPassphrase,
            $this->privProtocol,
            $this->privPassphrase,
        );
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
        $client->setV3(
            $oid,
            'i',
            (string) $threshold,
            $this->username,
            $this->securityLevel,
            $this->authProtocol,
            $this->authPassphrase,
            $this->privProtocol,
            $this->privPassphrase,
        );
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
        $client->setV3(
            $oid,
            'i',
            (string) $threshold,
            $this->username,
            $this->securityLevel,
            $this->authProtocol,
            $this->authPassphrase,
            $this->privProtocol,
            $this->privPassphrase,
        );
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
        $client->setV3(
            $oid,
            'i',
            (string) $threshold,
            $this->username,
            $this->securityLevel,
            $this->authProtocol,
            $this->authPassphrase,
            $this->privProtocol,
            $this->privPassphrase,
        );
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
            $client->setV3(
                $oid,
                'i',
                '2',
                $this->username,
                $this->securityLevel,
                $this->authProtocol,
                $this->authPassphrase,
                $this->privProtocol,
                $this->privPassphrase,
            );
        } catch (\Sunfox\ApcPdu\PduException $e) {
            // APC PDUs often drop the connection after processing reset commands
            // but the operation succeeds. Ignore connection-related errors.
            if (!str_contains($e->getMessage(), 'No message received from host')) {
                throw $e;
            }
        }
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
