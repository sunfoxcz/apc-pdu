# APC PDU AP8653 - SNMP Monitoring Library

## Overview

PHP library for reading data from APC PDU AP8XXX series via SNMP (v1 and v3).
Supports Network Port Sharing (up to 4 daisy-chained PDUs on one IP).

## Project Structure

```
├── src/
│   ├── ApcPdu.php            # Main class with v1/v3 and NPS support
│   ├── DeviceMetric.php      # Enum for device-level metrics
│   ├── PduOutletMetric.php   # Interface for outlet metrics
│   ├── OutletMetric.php      # Enum for outlet metrics
│   └── SnmpException.php     # Custom exception class
├── tests/
│   ├── Unit/                 # Unit tests (no PDU required)
│   └── Integration/          # Integration tests (requires PDU)
├── docs/
│   └── APC_PDU_AP8653_OID_Reference.txt
├── composer.json
├── phpunit.xml
├── phpcs.xml              # PSR-12 coding style configuration
├── Dockerfile
├── docker-compose.yml
├── .env.example
└── README.md
```

## Tested Device

- Model: AP8653 (Metered by Outlet with Switching)
- Rack PDU FW: 7.1.4
- APC OS: 7.1.2
- Outlets: 24 (21× C13 + 3× C19)

## Key OIDs

### Device Level

Base: `.1.3.6.1.4.1.318.1.1.26.4.3.1.{metric}.{pdu_index}`

| Metric | OID suffix | SNMP Units | Conversion |
|--------|------------|------------|------------|
| Power | .5 | hundredths kW | ×10 → W |
| Peak Power | .6 | hundredths kW | ×10 → W |
| Energy | .9 | tenths kWh | ÷10 → kWh |

### Outlet Level

Base: `.1.3.6.1.4.1.318.1.1.26.9.4.3.1.{metric}.{snmp_index}`

| Metric | OID suffix | SNMP Units | Conversion |
|--------|------------|------------|------------|
| Name | .3 | STRING | - |
| Index | .4 | INTEGER | - |
| Current | .6 | tenths A | ÷10 → A |
| Power | .7 | W | direct |
| Peak Power | .8 | W | direct |
| Energy | .11 | tenths kWh | ÷10 → kWh |

### Network Port Sharing

- Host PDU: device index = 1, outlet SNMP index = 1-24
- Guest PDU 1: device index = 2, outlet SNMP index = 25-48
- Guest PDU 2: device index = 3, outlet SNMP index = 49-72
- Guest PDU 3: device index = 4, outlet SNMP index = 73-96
- Formula: `snmp_index = ((pdu_index - 1) × 24) + outlet_number`

## Usage

```php
<?php
use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;

// SNMPv1
$pdu = ApcPdu::v1('192.168.1.100', 'public');

// SNMPv3
$pdu = ApcPdu::v3('192.168.1.100', 'monitor', 'AuthPass', 'PrivPass');

// Device metrics (PDU 1 is default)
$power = $pdu->getDevice(DeviceMetric::Power);       // W
$energy = $pdu->getDevice(DeviceMetric::Energy);     // kWh

// Device metrics for specific PDU (1-4)
$power = $pdu->getDevice(DeviceMetric::Power, 2);    // PDU 2
$power = $pdu->getDevice(DeviceMetric::Power, 3);    // PDU 3
$power = $pdu->getDevice(DeviceMetric::Power, 4);    // PDU 4

// Outlet metrics
$outletPower = $pdu->getOutletStatus(1, 5, OutletMetric::Power);
$outletName = $pdu->getOutletStatus(1, 5, OutletMetric::Name);

// Bulk operations
$device = $pdu->getDeviceAll(1);        // all device metrics for PDU 1
$outlet = $pdu->getOutletAll(1, 5);     // all outlet metrics
$outlets = $pdu->getAllOutlets(1);      // all outlets of PDU 1
$full = $pdu->getFullStatus();          // complete dump (all available PDUs)
```

## Development

### Running Tests with Docker

```bash
# Copy and edit credentials
cp .env.example .env

# Run all tests
docker compose run --rm php

# Unit tests only
docker compose run --rm unit

# Integration tests (requires PDU)
docker compose run --rm integration

# PHPStan analysis
docker compose run --rm phpstan

# PSR-12 coding style check
docker compose run --rm phpcs

# Auto-fix PSR-12 violations
docker compose run --rm phpcbf
```

## PDU SNMP Configuration

### Enable SNMPv3
1. Configuration → Network → SNMPv3 → Access → Enable
2. Configuration → Network → SNMPv3 → User Profiles
   - User Name: `monitor`
   - Auth Protocol: `SHA`
   - Auth Passphrase: min 8 chars
   - Privacy Protocol: `AES`
   - Privacy Passphrase: min 8 chars

### Test from Command Line

```bash
# SNMPv3 - device power
snmpget -v3 -l authPriv -u monitor -a SHA -A "AuthPass" -x AES -X "PrivPass" \
  <IP> .1.3.6.1.4.1.318.1.1.26.4.3.1.5.1
```

## Notes

- `snmpwalk` on subtree doesn't work, must use `snmpget` on specific OIDs
- After factory reset, SNMP community is empty
- Energy is counted from last reset
- PDU supports only SNMPv1 and SNMPv3 (not v2c)

## Preferences

- Do not add Co-Authored-By to commit messages
