<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp;

use Sunfox\ApcPdu\PduException;

final class SnmpResponseParser
{
    public function parseNumeric(string $raw): float
    {
        // Extract value after colon (e.g., "INTEGER: 1234" -> "1234", "Gauge32: 5000" -> "5000")
        if (preg_match('/:\s*([-]?\d+)/', $raw, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse SNMP value: {$raw}");
    }

    public function parseString(string $raw): string
    {
        $value = preg_replace('/^STRING:\s*"?|"?\s*$/', '', $raw);

        return trim($value ?? '');
    }
}
