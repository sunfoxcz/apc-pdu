<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * Interface for outlet-level PDU metrics
 */
interface PduOutletMetric
{
    public function value(): string;

    public function unit(): string;

    public function isString(): bool;
}
