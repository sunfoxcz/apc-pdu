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

    public function getDeviceMetric(DeviceMetric $metric, int $pduIndex): float
    {
        // APC CLI commands for device metrics
        // Note: Actual CLI commands may vary by firmware version
        $command = match ($metric) {
            DeviceMetric::Power => "phReading {$pduIndex} power",
            DeviceMetric::PeakPower => "phReading {$pduIndex} peakPower",
            DeviceMetric::Energy => "phReading {$pduIndex} energy",
        };

        $output = $this->client->execute($command);

        return match ($metric) {
            DeviceMetric::Power, DeviceMetric::PeakPower => $this->parser->parseDevicePower($output),
            DeviceMetric::Energy => $this->parser->parseDeviceEnergy($output),
        };
    }

    public function getOutletMetric(PduOutletMetric $metric, int $pduIndex, int $outletNumber): float|string
    {
        // Calculate global outlet number for NPS
        $globalOutlet = (($pduIndex - 1) * $this->outletsPerPdu) + $outletNumber;

        // APC CLI commands for outlet metrics
        $command = match (true) {
            $metric === OutletMetric::Name => "olName {$globalOutlet}",
            $metric === OutletMetric::Current => "olReading {$globalOutlet} current",
            $metric === OutletMetric::Power => "olReading {$globalOutlet} power",
            $metric === OutletMetric::PeakPower => "olReading {$globalOutlet} peakPower",
            $metric === OutletMetric::Energy => "olReading {$globalOutlet} energy",
            $metric === OutletMetric::Index => throw new PduException('Index metric not available via SSH'),
            default => throw new PduException('Unknown outlet metric'),
        };

        $output = $this->client->execute($command);

        return match (true) {
            $metric === OutletMetric::Name => $this->parser->parseOutletName($output),
            $metric === OutletMetric::Current => $this->parser->parseOutletCurrent($output),
            $metric === OutletMetric::Power, $metric === OutletMetric::PeakPower => $this->parser->parseOutletPower($output),
            $metric === OutletMetric::Energy => $this->parser->parseOutletEnergy($output),
            default => throw new PduException('Unknown outlet metric'),
        };
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
