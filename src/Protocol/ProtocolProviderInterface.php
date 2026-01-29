<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol;

use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\PduOutletMetric;

interface ProtocolProviderInterface
{
    /**
     * Get device-level metric value.
     *
     * @return float|int|string Value in standard units (W for power, kWh for energy)
     * @throws PduException
     */
    public function getDeviceMetric(DeviceMetric $metric, int $pduIndex): float|int|string;

    /**
     * Get all device-level metrics in a single batch request.
     *
     * @param int $pduIndex PDU index (1-4)
     * @return array<string, float|int|string> Map of metric value => converted value
     * @throws PduException
     */
    public function getDeviceMetricsBatch(int $pduIndex): array;

    /**
     * Get outlet-level metric value.
     *
     * @return float|int|string Value in standard units
     * @throws PduException
     */
    public function getOutletMetric(PduOutletMetric $metric, int $pduIndex, int $outletNumber): float|int|string;

    /**
     * Get all outlet-level metrics in a single batch request.
     *
     * @param int $pduIndex PDU index (1-4)
     * @param int $outletNumber Outlet number (1-N)
     * @return array<string, float|int|string> Map of metric value => converted value
     * @throws PduException
     */
    public function getOutletMetricsBatch(int $pduIndex, int $outletNumber): array;

    public function getHost(): string;

    public function getOutletsPerPdu(): int;
}
