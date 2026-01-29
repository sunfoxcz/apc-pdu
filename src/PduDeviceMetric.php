<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Interface for device-level PDU metrics
 */
interface PduDeviceMetric
{
    public function deviceIndex(): int;

    public function oidSuffix(): int;

    public function unit(): string;

    public function divisor(): float;
}
