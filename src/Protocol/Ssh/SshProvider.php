<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Ssh;

use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;
use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\PduOutletMetric;
use Sunfox\ApcPdu\Protocol\ProtocolProviderInterface;

final class SshProvider implements ProtocolProviderInterface
{
    private SshClient $client;
    private ApcCliParser $parser;

    public function __construct(
        string $host,
        string $username,
        string $password,
        private int $outletsPerPdu = 24,
        int $port = 22,
    ) {
        $this->client = new SshClient($host, $username, $password, $port);
        $this->parser = new ApcCliParser();
    }

    public function getDeviceMetric(DeviceMetric $metric, int $pduIndex): float|int|string
    {
        // APC CLI commands for device metrics
        // Format: devReading [id#:]<power | energy | appower | pf>
        $prefix = $pduIndex > 1 ? "{$pduIndex}:" : '';

        $command = match ($metric) {
            DeviceMetric::Power => "devReading {$prefix}power",
            DeviceMetric::Energy => "devReading {$prefix}energy",
            DeviceMetric::ApparentPower => "devReading {$prefix}appower",
            DeviceMetric::PowerFactor => "devReading {$prefix}pf",
            default => throw new PduException("Device metric {$metric->value} not available via SSH"),
        };

        $output = $this->client->execute($command);

        return match ($metric) {
            DeviceMetric::Power => $this->parser->parseDevicePower($output),
            DeviceMetric::Energy => $this->parser->parseDeviceEnergy($output),
            DeviceMetric::ApparentPower => $this->parser->parseApparentPower($output),
            DeviceMetric::PowerFactor => $this->parser->parsePowerFactor($output),
            default => throw new PduException("Device metric {$metric->value} not available via SSH"),
        };
    }

    /**
     * @inheritDoc
     */
    public function getDeviceMetricsBatch(int $pduIndex): array
    {
        // SSH doesn't support batching, get metrics one by one
        $results = [];
        $availableMetrics = [
            DeviceMetric::Power,
            DeviceMetric::Energy,
            DeviceMetric::ApparentPower,
            DeviceMetric::PowerFactor,
        ];

        foreach ($availableMetrics as $metric) {
            try {
                $results[$metric->value] = $this->getDeviceMetric($metric, $pduIndex);
            } catch (PduException) {
                // Skip unavailable metrics
            }
        }

        return $results;
    }

    public function getOutletMetric(PduOutletMetric $metric, int $pduIndex, int $outletNumber): float|int|string
    {
        // Calculate global outlet number for NPS
        $globalOutlet = (($pduIndex - 1) * $this->outletsPerPdu) + $outletNumber;

        // APC CLI commands for outlet metrics
        // Format: olReading <outlet#> <current | power | energy>
        //         olName <outlet#>
        $command = match (true) {
            $metric === OutletMetric::Name => "olName {$globalOutlet}",
            $metric === OutletMetric::Current => "olReading {$globalOutlet} current",
            $metric === OutletMetric::Power => "olReading {$globalOutlet} power",
            $metric === OutletMetric::Energy => "olReading {$globalOutlet} energy",
            $metric === OutletMetric::Index => throw new PduException('Index metric not available via SSH'),
            default => throw new PduException('Outlet metric not available via SSH'),
        };

        $output = $this->client->execute($command);

        return match (true) {
            $metric === OutletMetric::Name => $this->parser->parseOutletName($output),
            $metric === OutletMetric::Current => $this->parser->parseOutletCurrent($output),
            $metric === OutletMetric::Power => $this->parser->parseOutletPower($output),
            $metric === OutletMetric::Energy => $this->parser->parseOutletEnergy($output),
            default => throw new PduException('Outlet metric not available via SSH'),
        };
    }

    /**
     * @inheritDoc
     */
    public function getOutletMetricsBatch(int $pduIndex, int $outletNumber): array
    {
        // SSH doesn't support batching, get metrics one by one
        $results = [];
        $availableMetrics = [
            OutletMetric::Name,
            OutletMetric::Current,
            OutletMetric::Power,
            OutletMetric::Energy,
        ];

        foreach ($availableMetrics as $metric) {
            try {
                $results[$metric->value] = $this->getOutletMetric($metric, $pduIndex, $outletNumber);
            } catch (PduException) {
                // Skip unavailable metrics
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
