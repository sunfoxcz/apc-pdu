<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Interface for outlet-level PDU metrics
 */
interface PduOutletMetric
{
    public function oidSuffix(): int;

    public function unit(): string;

    public function divisor(): float;

    public function isString(): bool;
}
