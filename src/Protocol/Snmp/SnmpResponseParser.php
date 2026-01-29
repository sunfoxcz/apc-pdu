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

        // Handle raw numeric values (e.g., from FreeDSx client)
        $value = trim($raw, " \t\n\r\0\x0B\"");
        if (is_numeric($value)) {
            return (float) $value;
        }

        throw new PduException("Could not parse SNMP value: {$raw}");
    }

    /**
     * Parse numeric value from batch output (values only, no type prefix).
     *
     * With -Oqv flag, snmpget returns just the value without type prefix.
     * Examples: "1234", "5000", "-10"
     */
    public function parseNumericBatch(string $raw): float
    {
        $value = trim($raw, " \t\n\r\0\x0B\"");

        if (is_numeric($value)) {
            return (float) $value;
        }

        // Fallback to standard parsing if there's a type prefix
        return $this->parseNumeric($raw);
    }

    public function parseString(string $raw): string
    {
        // Handle type prefix format (e.g., 'STRING: "value"')
        if (str_starts_with($raw, 'STRING:')) {
            $value = preg_replace('/^STRING:\s*"?|"?\s*$/', '', $raw);
            return trim($value ?? '');
        }

        // Handle raw string values (e.g., from FreeDSx client)
        return trim($raw, " \t\n\r\0\x0B\"");
    }

    /**
     * Parse string value from batch output (values only, no type prefix).
     *
     * With -Oqv flag, snmpget returns just the value, possibly quoted.
     */
    public function parseStringBatch(string $raw): string
    {
        return trim($raw, " \t\n\r\0\x0B\"");
    }
}
