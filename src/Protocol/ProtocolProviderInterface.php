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
     * @return float Value in standard units (W for power, kWh for energy)
     * @throws PduException
     */
    public function getDeviceMetric(DeviceMetric $metric, int $pduIndex): float;

    /**
     * Get outlet-level metric value.
     *
     * @return float|string Value in standard units
     * @throws PduException
     */
    public function getOutletMetric(PduOutletMetric $metric, int $pduIndex, int $outletNumber): float|string;

    public function getHost(): string;

    public function getOutletsPerPdu(): int;
}
