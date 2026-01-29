<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Ssh;

use Sunfox\ApcPdu\PduException;

final class ApcCliParser
{
    /**
     * Parse device power from CLI output.
     * Example output: "Power: 1234 W"
     */
    public function parseDevicePower(string $output): float
    {
        if (preg_match('/Power:\s*([\d.]+)\s*W/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse device power from: {$output}");
    }

    /**
     * Parse device energy from CLI output.
     * Example output: "Energy: 12.34 kWh"
     */
    public function parseDeviceEnergy(string $output): float
    {
        if (preg_match('/Energy:\s*([\d.]+)\s*kWh/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse device energy from: {$output}");
    }

    /**
     * Parse outlet name from CLI output.
     */
    public function parseOutletName(string $output): string
    {
        if (preg_match('/Name:\s*(.+)$/m', $output, $matches)) {
            return trim($matches[1]);
        }

        throw new PduException("Could not parse outlet name from: {$output}");
    }

    /**
     * Parse outlet current from CLI output.
     * Example output: "Current: 1.2 A"
     */
    public function parseOutletCurrent(string $output): float
    {
        if (preg_match('/Current:\s*([\d.]+)\s*A/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse outlet current from: {$output}");
    }

    /**
     * Parse outlet power from CLI output.
     */
    public function parseOutletPower(string $output): float
    {
        if (preg_match('/Power:\s*([\d.]+)\s*W/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse outlet power from: {$output}");
    }

    /**
     * Parse outlet energy from CLI output.
     */
    public function parseOutletEnergy(string $output): float
    {
        if (preg_match('/Energy:\s*([\d.]+)\s*kWh/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse outlet energy from: {$output}");
    }
}
