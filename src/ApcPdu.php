<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu;

/**
 * APC PDU SNMP Reader with Network Port Sharing support
 *
 * Supports SNMPv1 and SNMPv3, reading from host and guest PDU.
 * Tested on AP8653.
 */
class ApcPdu
{
    private string $host;
    private int $outletsPerPdu;

    // SNMP v1
    private ?string $community = null;

    // SNMP v3
    private ?string $username = null;
    private ?string $authPassphrase = null;
    private ?string $privPassphrase = null;
    private string $authProtocol = 'SHA';
    private string $privProtocol = 'AES';
    private string $securityLevel = 'authPriv';

    private int $timeout;
    private int $retries;

    // Base OIDs
    private const OID_DEVICE = '.1.3.6.1.4.1.318.1.1.26.4.3.1';
    private const OID_OUTLET = '.1.3.6.1.4.1.318.1.1.26.9.4.3.1';

    /**
     * Create instance for SNMPv1
     */
    public static function v1(
        string $host,
        string $community = 'public',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3
    ): self {
        $instance = new self($host, $outletsPerPdu, $timeout, $retries);
        $instance->community = $community;
        return $instance;
    }

    /**
     * Create instance for SNMPv3
     */
    public static function v3(
        string $host,
        string $username,
        string $authPassphrase,
        string $privPassphrase = '',
        string $authProtocol = 'SHA',
        string $privProtocol = 'AES',
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3
    ): self {
        $instance = new self($host, $outletsPerPdu, $timeout, $retries);
        $instance->username = $username;
        $instance->authPassphrase = $authPassphrase;
        $instance->privPassphrase = $privPassphrase;
        $instance->authProtocol = $authProtocol;
        $instance->privProtocol = $privProtocol;

        if (!empty($privPassphrase)) {
            $instance->securityLevel = 'authPriv';
        } elseif (!empty($authPassphrase)) {
            $instance->securityLevel = 'authNoPriv';
        } else {
            $instance->securityLevel = 'noAuthNoPriv';
        }

        return $instance;
    }

    private function __construct(
        string $host,
        int $outletsPerPdu = 24,
        int $timeout = 1000000,
        int $retries = 3
    ) {
        $this->host = $host;
        $this->outletsPerPdu = $outletsPerPdu;
        $this->timeout = $timeout;
        $this->retries = $retries;
    }

    /**
     * Get a single device-level metric.
     *
     * @param PduDeviceMetric $metric PDU1::Power, PDU2::Energy, etc.
     * @return float Value in target units (W, kWh)
     */
    public function getDeviceStatus(PduDeviceMetric $metric): float
    {
        $oid = self::OID_DEVICE . ".{$metric->oidSuffix()}.{$metric->deviceIndex()}";
        $raw = $this->snmpGetValue($oid);
        return $raw / $metric->divisor();
    }

    /**
     * Get all device-level metrics for one PDU.
     *
     * @param int $pduIndex 1 = host, 2 = guest
     * @return array{power_w: float, peak_power_w: float, energy_kwh: float}
     */
    public function getDeviceAll(int $pduIndex = 1): array
    {
        $enum = $pduIndex === 1 ? PDU1::class : PDU2::class;

        return [
            'power_w' => $this->getDeviceStatus($enum::Power),
            'peak_power_w' => $this->getDeviceStatus($enum::PeakPower),
            'energy_kwh' => $this->getDeviceStatus($enum::Energy),
        ];
    }

    /**
     * Get a single outlet-level metric.
     *
     * @param int $pduIndex 1 = host, 2 = guest
     * @param int $outletNumber Outlet number on the given PDU (1-24)
     * @param PduOutletMetric $metric OutletMetric::Power, etc.
     * @return float|string Value in target units
     */
    public function getOutletStatus(int $pduIndex, int $outletNumber, PduOutletMetric $metric): float|string
    {
        $snmpIndex = $this->outletToSnmpIndex($pduIndex, $outletNumber);
        $oid = self::OID_OUTLET . ".{$metric->oidSuffix()}.{$snmpIndex}";

        if ($metric->isString()) {
            return $this->snmpGetString($oid);
        }

        $raw = $this->snmpGetValue($oid);
        return $raw / $metric->divisor();
    }

    /**
     * Get all metrics for one outlet.
     *
     * @param int $pduIndex 1 = host, 2 = guest
     * @param int $outletNumber Outlet number on the given PDU (1-24)
     * @return array{name: string, current_a: float, power_w: float, peak_power_w: float, energy_kwh: float}
     */
    public function getOutletAll(int $pduIndex, int $outletNumber): array
    {
        return [
            'name' => $this->getOutletStatus($pduIndex, $outletNumber, OutletMetric::Name),
            'current_a' => $this->getOutletStatus($pduIndex, $outletNumber, OutletMetric::Current),
            'power_w' => $this->getOutletStatus($pduIndex, $outletNumber, OutletMetric::Power),
            'peak_power_w' => $this->getOutletStatus($pduIndex, $outletNumber, OutletMetric::PeakPower),
            'energy_kwh' => $this->getOutletStatus($pduIndex, $outletNumber, OutletMetric::Energy),
        ];
    }

    /**
     * Get all outlets for one PDU.
     *
     * @param int $pduIndex 1 = host, 2 = guest
     * @return array<int, array{name: string, current_a: float, power_w: float, peak_power_w: float, energy_kwh: float}>
     */
    public function getAllOutlets(int $pduIndex = 1): array
    {
        $outlets = [];

        for ($i = 1; $i <= $this->outletsPerPdu; $i++) {
            try {
                $outlets[$i] = $this->getOutletAll($pduIndex, $i);
            } catch (SnmpException $e) {
                continue;
            }
        }

        return $outlets;
    }

    /**
     * Get complete status of both PDUs.
     *
     * @return array<string, array{device: array, outlets: array}>
     */
    public function getFullStatus(): array
    {
        $result = [];

        foreach ([1, 2] as $pduIndex) {
            try {
                $result["pdu{$pduIndex}"] = [
                    'device' => $this->getDeviceAll($pduIndex),
                    'outlets' => $this->getAllOutlets($pduIndex),
                ];
            } catch (SnmpException $e) {
                // PDU does not exist
                continue;
            }
        }

        return $result;
    }

    /**
     * Test connection to PDU.
     */
    public function testConnection(int $pduIndex = 1): bool
    {
        try {
            $enum = $pduIndex === 1 ? PDU1::class : PDU2::class;
            $this->getDeviceStatus($enum::Power);
            return true;
        } catch (SnmpException $e) {
            return false;
        }
    }

    /**
     * Get the host address.
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Get the number of outlets per PDU.
     */
    public function getOutletsPerPdu(): int
    {
        return $this->outletsPerPdu;
    }

    /**
     * Convert PDU index + outlet number to SNMP index.
     *
     * PDU 1, outlet 1-24 → SNMP index 1-24
     * PDU 2, outlet 1-24 → SNMP index 25-48
     */
    private function outletToSnmpIndex(int $pduIndex, int $outletNumber): int
    {
        return (($pduIndex - 1) * $this->outletsPerPdu) + $outletNumber;
    }

    /**
     * Execute SNMP GET and return numeric value.
     */
    private function snmpGetValue(string $oid): float
    {
        $result = $this->snmpGet($oid);

        if (preg_match('/[-]?\d+/', $result, $matches)) {
            return (float) $matches[0];
        }

        throw new SnmpException("Could not parse SNMP value: {$result}");
    }

    /**
     * Execute SNMP GET and return string value.
     */
    private function snmpGetString(string $oid): string
    {
        $result = $this->snmpGet($oid);
        $value = preg_replace('/^STRING:\s*"?|"?\s*$/', '', $result);
        return trim($value ?? '');
    }

    /**
     * Execute SNMP GET (v1 or v3 based on configuration).
     */
    private function snmpGet(string $oid): string
    {
        if ($this->community !== null) {
            // SNMPv1
            $result = @snmpget(
                $this->host,
                $this->community,
                $oid,
                $this->timeout,
                $this->retries
            );
        } else {
            // SNMPv3
            $result = @snmp3_get(
                $this->host,
                $this->username,
                $this->securityLevel,
                $this->authProtocol,
                $this->authPassphrase,
                $this->privProtocol,
                $this->privPassphrase,
                $oid,
                $this->timeout,
                $this->retries
            );
        }

        if ($result === false) {
            $error = error_get_last();
            throw new SnmpException(
                "SNMP GET failed for OID: {$oid}" .
                ($error ? " - " . $error['message'] : "")
            );
        }

        return $result;
    }
}
