<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

use Sunfox\ApcPdu\Dto\DeviceStatus;
use Sunfox\ApcPdu\Dto\OutletStatus;
use Sunfox\ApcPdu\Dto\PduInfo;
use Sunfox\ApcPdu\Protocol\ProtocolProviderInterface;

/**
 * APC PDU Reader with Network Port Sharing support
 *
 * Protocol-agnostic facade supporting SNMP and SSH protocols.
 * Tested on AP8653.
 */
final class ApcPdu
{
    public function __construct(
        private ProtocolProviderInterface $protocol,
    ) {
    }

    /**
     * Get a single device-level metric.
     *
     * @param DeviceMetric $metric DeviceMetric::Power, DeviceMetric::Energy, etc.
     * @param int $pduIndex PDU index (1-4, default 1)
     * @return float|int|string Value in target units (W, kWh)
     */
    public function getDevice(DeviceMetric $metric, int $pduIndex = 1): float|int|string
    {
        return $this->protocol->getDeviceMetric($metric, $pduIndex);
    }

    /**
     * Get all device-level metrics as a DTO.
     *
     * Uses batch requests to fetch all metrics in a single SNMP call.
     *
     * @param int $pduIndex PDU index (1-4, default 1)
     */
    public function getDeviceStatus(int $pduIndex = 1): DeviceStatus
    {
        $metrics = $this->protocol->getDeviceMetricsBatch($pduIndex);

        $loadStatusValue = (int) ($metrics[DeviceMetric::LoadStatus->value] ?? 1);
        $loadStatus = LoadStatus::tryFrom($loadStatusValue) ?? LoadStatus::Normal;

        return new DeviceStatus(
            moduleIndex: (int) ($metrics[DeviceMetric::ModuleIndex->value] ?? 0),
            pduIndex: (int) ($metrics[DeviceMetric::PduIndex->value] ?? 0),
            name: (string) ($metrics[DeviceMetric::Name->value] ?? ''),
            loadStatus: $loadStatus,
            powerW: (float) ($metrics[DeviceMetric::Power->value] ?? 0.0),
            peakPowerW: (float) ($metrics[DeviceMetric::PeakPower->value] ?? 0.0),
            peakPowerTimestamp: (string) ($metrics[DeviceMetric::PeakPowerTimestamp->value] ?? ''),
            energyResetTimestamp: (string) ($metrics[DeviceMetric::EnergyResetTimestamp->value] ?? ''),
            energyKwh: (float) ($metrics[DeviceMetric::Energy->value] ?? 0.0),
            energyStartTimestamp: (string) ($metrics[DeviceMetric::EnergyStartTimestamp->value] ?? ''),
            apparentPowerVa: (float) ($metrics[DeviceMetric::ApparentPower->value] ?? 0.0),
            powerFactor: (float) ($metrics[DeviceMetric::PowerFactor->value] ?? 0.0),
            outletCount: (int) ($metrics[DeviceMetric::OutletCount->value] ?? 0),
            phaseCount: (int) ($metrics[DeviceMetric::PhaseCount->value] ?? 0),
            peakPowerResetTimestamp: (string) ($metrics[DeviceMetric::PeakPowerResetTimestamp->value] ?? ''),
            lowLoadThreshold: (int) ($metrics[DeviceMetric::LowLoadThreshold->value] ?? 0),
            nearOverloadThreshold: (int) ($metrics[DeviceMetric::NearOverloadThreshold->value] ?? 0),
            overloadRestriction: (int) ($metrics[DeviceMetric::OverloadRestriction->value] ?? 0),
        );
    }

    /**
     * Get a single outlet-level metric.
     *
     * @param int $pduIndex PDU index (1-4)
     * @param int $outletNumber Outlet number on the given PDU (1-24)
     * @param PduOutletMetric $metric OutletMetric::Power, etc.
     * @return float|int|string Value in target units
     */
    public function getOutlet(int $pduIndex, int $outletNumber, PduOutletMetric $metric): float|int|string
    {
        return $this->protocol->getOutletMetric($metric, $pduIndex, $outletNumber);
    }

    /**
     * Get all metrics for one outlet as a DTO.
     *
     * Uses batch requests to fetch all metrics in a single SNMP call.
     *
     * @param int $pduIndex PDU index (1-4)
     * @param int $outletNumber Outlet number on the given PDU (1-24)
     */
    public function getOutletStatus(int $pduIndex, int $outletNumber): OutletStatus
    {
        $metrics = $this->protocol->getOutletMetricsBatch($pduIndex, $outletNumber);

        $stateValue = (int) ($metrics[OutletMetric::State->value] ?? 1);
        $state = PowerState::tryFrom($stateValue) ?? PowerState::Off;

        return new OutletStatus(
            moduleIndex: (int) ($metrics[OutletMetric::ModuleIndex->value] ?? 0),
            pduIndex: (int) ($metrics[OutletMetric::PduIndex->value] ?? 0),
            name: (string) ($metrics[OutletMetric::Name->value] ?? ''),
            index: (int) ($metrics[OutletMetric::Index->value] ?? 0),
            state: $state,
            currentA: (float) ($metrics[OutletMetric::Current->value] ?? 0.0),
            powerW: (float) ($metrics[OutletMetric::Power->value] ?? 0.0),
            peakPowerW: (float) ($metrics[OutletMetric::PeakPower->value] ?? 0.0),
            peakPowerTimestamp: (string) ($metrics[OutletMetric::PeakPowerTimestamp->value] ?? ''),
            energyResetTimestamp: (string) ($metrics[OutletMetric::EnergyResetTimestamp->value] ?? ''),
            energyKwh: (float) ($metrics[OutletMetric::Energy->value] ?? 0.0),
            outletType: (string) ($metrics[OutletMetric::OutletType->value] ?? ''),
            externalLink: (string) ($metrics[OutletMetric::ExternalLink->value] ?? ''),
        );
    }

    /**
     * Get all outlets for one PDU.
     *
     * @param int $pduIndex PDU index (1-4, default 1)
     * @return array<int, OutletStatus>
     */
    public function getAllOutlets(int $pduIndex = 1): array
    {
        $outlets = [];

        for ($i = 1; $i <= $this->protocol->getOutletsPerPdu(); $i++) {
            try {
                $outlets[$i] = $this->getOutletStatus($pduIndex, $i);
            } catch (PduException) {
                continue;
            }
        }

        return $outlets;
    }

    /**
     * Get complete status for one PDU.
     *
     * @param int $pduIndex PDU index (1-4, default 1)
     */
    public function getPduInfo(int $pduIndex = 1): PduInfo
    {
        return new PduInfo(
            pduIndex: $pduIndex,
            device: $this->getDeviceStatus($pduIndex),
            outlets: $this->getAllOutlets($pduIndex),
        );
    }

    /**
     * Get complete status of all available PDUs.
     *
     * Iterates through PDU indices 1-4 and stops when an error occurs.
     *
     * @return array<int, PduInfo>
     */
    public function getFullStatus(): array
    {
        $result = [];

        for ($pduIndex = 1; $pduIndex <= 4; $pduIndex++) {
            try {
                $result[$pduIndex] = $this->getPduInfo($pduIndex);
            } catch (PduException) {
                // PDU does not exist, stop iterating
                break;
            }
        }

        return $result;
    }

    /**
     * Test connection to PDU.
     *
     * @param int $pduIndex PDU index (1-4, default 1)
     */
    public function testConnection(int $pduIndex = 1): bool
    {
        try {
            $this->protocol->getDeviceMetric(DeviceMetric::Power, $pduIndex);
            return true;
        } catch (PduException) {
            return false;
        }
    }

    /**
     * Get the host address.
     */
    public function getHost(): string
    {
        return $this->protocol->getHost();
    }

    /**
     * Get the number of outlets per PDU.
     */
    public function getOutletsPerPdu(): int
    {
        return $this->protocol->getOutletsPerPdu();
    }
}
