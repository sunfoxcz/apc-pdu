<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

use Sunfox\ApcPdu\Dto\OutletStatus;
use Sunfox\ApcPdu\Protocol\ProtocolProviderInterface;
use Sunfox\ApcPdu\Protocol\WritableProtocolProviderInterface;

/**
 * Represents a single outlet on an APC PDU.
 *
 * Provides read access to outlet metrics and optional write access
 * to outlet name if the underlying protocol supports it.
 */
final class ApcPduOutlet
{
    public function __construct(
        private ProtocolProviderInterface $protocol,
        private int $pduIndex,
        private int $outletNumber,
    ) {
    }

    /**
     * Get the PDU index this outlet belongs to.
     */
    public function getPduIndex(): int
    {
        return $this->pduIndex;
    }

    /**
     * Get the outlet number on the PDU.
     */
    public function getOutletNumber(): int
    {
        return $this->outletNumber;
    }

    /**
     * Get a single outlet-level metric.
     *
     * @param PduOutletMetric $metric OutletMetric::Power, etc.
     * @return float|int|string Value in target units
     */
    public function getMetric(PduOutletMetric $metric): float|int|string
    {
        return $this->protocol->getOutletMetric($metric, $this->pduIndex, $this->outletNumber);
    }

    /**
     * Get the outlet name.
     */
    public function getName(): string
    {
        return (string) $this->protocol->getOutletMetric(OutletMetric::Name, $this->pduIndex, $this->outletNumber);
    }

    /**
     * Get the outlet index (physical position).
     */
    public function getIndex(): int
    {
        return (int) $this->protocol->getOutletMetric(OutletMetric::Index, $this->pduIndex, $this->outletNumber);
    }

    /**
     * Get the outlet power state.
     */
    public function getState(): PowerState
    {
        $value = (int) $this->protocol->getOutletMetric(OutletMetric::State, $this->pduIndex, $this->outletNumber);

        return PowerState::tryFrom($value) ?? PowerState::Off;
    }

    /**
     * Get the current draw in Amperes.
     */
    public function getCurrent(): float
    {
        return (float) $this->protocol->getOutletMetric(OutletMetric::Current, $this->pduIndex, $this->outletNumber);
    }

    /**
     * Get the power consumption in Watts.
     */
    public function getPower(): float
    {
        return (float) $this->protocol->getOutletMetric(OutletMetric::Power, $this->pduIndex, $this->outletNumber);
    }

    /**
     * Get the peak power in Watts.
     */
    public function getPeakPower(): float
    {
        return (float) $this->protocol->getOutletMetric(OutletMetric::PeakPower, $this->pduIndex, $this->outletNumber);
    }

    /**
     * Get the energy consumption in kWh.
     */
    public function getEnergy(): float
    {
        return (float) $this->protocol->getOutletMetric(OutletMetric::Energy, $this->pduIndex, $this->outletNumber);
    }

    /**
     * Get the outlet type (e.g., "IEC C13").
     */
    public function getOutletType(): string
    {
        $metric = OutletMetric::OutletType;

        return (string) $this->protocol->getOutletMetric($metric, $this->pduIndex, $this->outletNumber);
    }

    /**
     * Get all outlet metrics as a status DTO.
     *
     * Uses batch requests to fetch all metrics in a single call.
     */
    public function getStatus(): OutletStatus
    {
        $metrics = $this->protocol->getOutletMetricsBatch($this->pduIndex, $this->outletNumber);

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
            peakPowerStartTime: (string) ($metrics[OutletMetric::PeakPowerStartTime->value] ?? ''),
            energyKwh: (float) ($metrics[OutletMetric::Energy->value] ?? 0.0),
            energyStartTime: (string) ($metrics[OutletMetric::EnergyStartTime->value] ?? ''),
            outletType: (string) ($metrics[OutletMetric::OutletType->value] ?? ''),
            externalLink: (string) ($metrics[OutletMetric::ExternalLink->value] ?? ''),
        );
    }

    /**
     * Set the outlet name.
     *
     * @throws PduException If the protocol does not support write operations
     */
    public function setName(string $name): void
    {
        if (!$this->isWritable()) {
            throw new PduException('Protocol does not support write operations');
        }

        /** @var WritableProtocolProviderInterface $protocol */
        $protocol = $this->protocol;
        $protocol->setOutletName($this->pduIndex, $this->outletNumber, $name);
    }

    /**
     * Control the outlet power state.
     *
     * @throws PduException If the protocol does not support write operations
     */
    public function setState(OutletCommand $command): void
    {
        if (!$this->isWritable()) {
            throw new PduException('Protocol does not support write operations');
        }

        /** @var WritableProtocolProviderInterface $protocol */
        $protocol = $this->protocol;
        $protocol->setOutletState($this->pduIndex, $this->outletNumber, $command);
    }

    /**
     * Set the external link URL for the outlet.
     *
     * @throws PduException If the protocol does not support write operations
     */
    public function setExternalLink(string $url): void
    {
        if (!$this->isWritable()) {
            throw new PduException('Protocol does not support write operations');
        }

        /** @var WritableProtocolProviderInterface $protocol */
        $protocol = $this->protocol;
        $protocol->setOutletExternalLink($this->pduIndex, $this->outletNumber, $url);
    }

    /**
     * Set the low load power threshold for the outlet.
     *
     * @param int $watts Threshold in Watts
     * @throws PduException If the protocol does not support write operations
     */
    public function setLowLoadThreshold(int $watts): void
    {
        if (!$this->isWritable()) {
            throw new PduException('Protocol does not support write operations');
        }

        /** @var WritableProtocolProviderInterface $protocol */
        $protocol = $this->protocol;
        $protocol->setOutletLowLoadThreshold($this->pduIndex, $this->outletNumber, $watts);
    }

    /**
     * Set the near overload power threshold for the outlet.
     *
     * @param int $watts Threshold in Watts
     * @throws PduException If the protocol does not support write operations
     */
    public function setNearOverloadThreshold(int $watts): void
    {
        if (!$this->isWritable()) {
            throw new PduException('Protocol does not support write operations');
        }

        /** @var WritableProtocolProviderInterface $protocol */
        $protocol = $this->protocol;
        $protocol->setOutletNearOverloadThreshold($this->pduIndex, $this->outletNumber, $watts);
    }

    /**
     * Set the overload power threshold for the outlet.
     *
     * @param int $watts Threshold in Watts
     * @throws PduException If the protocol does not support write operations
     */
    public function setOverloadThreshold(int $watts): void
    {
        if (!$this->isWritable()) {
            throw new PduException('Protocol does not support write operations');
        }

        /** @var WritableProtocolProviderInterface $protocol */
        $protocol = $this->protocol;
        $protocol->setOutletOverloadThreshold($this->pduIndex, $this->outletNumber, $watts);
    }

    /**
     * Get the external link URL for the outlet.
     */
    public function getExternalLink(): string
    {
        $metric = OutletMetric::ExternalLink;

        return (string) $this->protocol->getOutletMetric($metric, $this->pduIndex, $this->outletNumber);
    }

    /**
     * Check if the outlet supports write operations.
     */
    public function isWritable(): bool
    {
        if (!$this->protocol instanceof WritableProtocolProviderInterface) {
            return false;
        }

        return $this->protocol->isWritable();
    }
}
