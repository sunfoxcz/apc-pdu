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
     * @return float Value in target units (W, kWh)
     */
    public function getDevice(DeviceMetric $metric, int $pduIndex = 1): float
    {
        return $this->protocol->getDeviceMetric($metric, $pduIndex);
    }

    /**
     * Get all device-level metrics as a DTO.
     *
     * @param int $pduIndex PDU index (1-4, default 1)
     */
    public function getDeviceStatus(int $pduIndex = 1): DeviceStatus
    {
        return new DeviceStatus(
            powerW: $this->protocol->getDeviceMetric(DeviceMetric::Power, $pduIndex),
            peakPowerW: $this->protocol->getDeviceMetric(DeviceMetric::PeakPower, $pduIndex),
            energyKwh: $this->protocol->getDeviceMetric(DeviceMetric::Energy, $pduIndex),
        );
    }

    /**
     * Get a single outlet-level metric.
     *
     * @param int $pduIndex PDU index (1-4)
     * @param int $outletNumber Outlet number on the given PDU (1-24)
     * @param PduOutletMetric $metric OutletMetric::Power, etc.
     * @return float|string Value in target units
     */
    public function getOutlet(int $pduIndex, int $outletNumber, PduOutletMetric $metric): float|string
    {
        return $this->protocol->getOutletMetric($metric, $pduIndex, $outletNumber);
    }

    /**
     * Get all metrics for one outlet as a DTO.
     *
     * @param int $pduIndex PDU index (1-4)
     * @param int $outletNumber Outlet number on the given PDU (1-24)
     */
    public function getOutletStatus(int $pduIndex, int $outletNumber): OutletStatus
    {
        return new OutletStatus(
            number: $outletNumber,
            name: (string) $this->protocol->getOutletMetric(OutletMetric::Name, $pduIndex, $outletNumber),
            currentA: (float) $this->protocol->getOutletMetric(OutletMetric::Current, $pduIndex, $outletNumber),
            powerW: (float) $this->protocol->getOutletMetric(OutletMetric::Power, $pduIndex, $outletNumber),
            peakPowerW: (float) $this->protocol->getOutletMetric(OutletMetric::PeakPower, $pduIndex, $outletNumber),
            energyKwh: (float) $this->protocol->getOutletMetric(OutletMetric::Energy, $pduIndex, $outletNumber),
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
