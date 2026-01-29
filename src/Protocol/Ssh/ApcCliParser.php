<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Ssh;

use Sunfox\ApcPdu\PduException;

final class ApcCliParser
{
    /**
     * Parse device power from CLI output.
     * Example output: "devReading power\nE000: Success\n0.5 kW\n\napc>"
     */
    public function parseDevicePower(string $output): float
    {
        // Power is returned in kW, convert to W
        if (preg_match('/E000:\s*Success\s*\n([\d.]+)\s*kW/i', $output, $matches)) {
            return (float) $matches[1] * 1000;
        }

        // Also try W format
        if (preg_match('/E000:\s*Success\s*\n([\d.]+)\s*W/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse device power from: {$output}");
    }

    /**
     * Parse device energy from CLI output.
     * Example output: "devReading energy\nE000: Success\n123.4 kWh\n\napc>"
     */
    public function parseDeviceEnergy(string $output): float
    {
        if (preg_match('/E000:\s*Success\s*\n([\d.]+)\s*kWh/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse device energy from: {$output}");
    }

    /**
     * Parse apparent power from CLI output.
     * Example output: "devReading appower\nE000: Success\n0.6 kVA\n\napc>"
     */
    public function parseApparentPower(string $output): float
    {
        // Apparent power is returned in kVA, convert to VA
        if (preg_match('/E000:\s*Success\s*\n([\d.]+)\s*kVA/i', $output, $matches)) {
            return (float) $matches[1] * 1000;
        }

        // Also try VA format
        if (preg_match('/E000:\s*Success\s*\n([\d.]+)\s*VA/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse apparent power from: {$output}");
    }

    /**
     * Parse power factor from CLI output.
     * Example output: "devReading pf\nE000: Success\n0.85\n\napc>"
     */
    public function parsePowerFactor(string $output): float
    {
        if (preg_match('/E000:\s*Success\s*\n([\d.]+)\s*\n/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse power factor from: {$output}");
    }

    /**
     * Parse outlet name from CLI output.
     * Example output: "olName 1\n 1: sm51\nE000: Success\n\napc>"
     */
    public function parseOutletName(string $output): string
    {
        // Format: " 1: outlet_name"
        if (preg_match('/\d+:\s*(.+?)\s*\n.*E000:\s*Success/is', $output, $matches)) {
            return trim($matches[1]);
        }

        throw new PduException("Could not parse outlet name from: {$output}");
    }

    /**
     * Parse outlet current from CLI output.
     * Example output: "olReading 1 current\n 1: sm51: 0.5 A\nE000: Success\n\napc>"
     */
    public function parseOutletCurrent(string $output): float
    {
        // Format: " 1: outlet_name: 0.5 A"
        if (preg_match('/\d+:\s*[^:]+:\s*([\d.]+)\s*A/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse outlet current from: {$output}");
    }

    /**
     * Parse outlet power from CLI output.
     * Example output: "olReading 1 power\n 1: sm51: 0 W\nE000: Success\n\napc>"
     */
    public function parseOutletPower(string $output): float
    {
        // Format: " 1: outlet_name: 0 W"
        if (preg_match('/\d+:\s*[^:]+:\s*([\d.]+)\s*W/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse outlet power from: {$output}");
    }

    /**
     * Parse outlet energy from CLI output.
     * Example output: "olReading 1 energy\n 1: sm51: 12.34 kWh\nE000: Success\n\napc>"
     */
    public function parseOutletEnergy(string $output): float
    {
        // Format: " 1: outlet_name: 12.34 kWh"
        if (preg_match('/\d+:\s*[^:]+:\s*([\d.]+)\s*kWh/i', $output, $matches)) {
            return (float) $matches[1];
        }

        throw new PduException("Could not parse outlet energy from: {$output}");
    }
}
