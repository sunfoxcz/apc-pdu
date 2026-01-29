# APC PDU SNMP Library

PHP library for reading data from APC PDU AP8XXX series via SNMP (v1 and v3) with Network Port Sharing support.

## Requirements

- PHP 8.1+
- ext-snmp

## Installation

```bash
composer require sunfox/apc-pdu
```

## Usage

### SNMPv1 Connection

```php
use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;

$pdu = ApcPdu::v1('192.168.1.100', 'public');
```

### SNMPv3 Connection

```php
$pdu = ApcPdu::v3(
    '192.168.1.100',
    'monitor',
    'AuthPassphrase',
    'PrivPassphrase'
);
```

### Device-Level Metrics

```php
// Get individual metrics (PDU 1 is default)
$power = $pdu->getDevice(DeviceMetric::Power);       // Returns watts
$peak = $pdu->getDevice(DeviceMetric::PeakPower);    // Returns watts
$energy = $pdu->getDevice(DeviceMetric::Energy);     // Returns kWh

// Get all device metrics at once
$device = $pdu->getDeviceAll();
// Returns: ['power_w' => 1234.5, 'peak_power_w' => 1500.0, 'energy_kwh' => 567.8]
```

### Outlet-Level Metrics

```php
// Get individual outlet metrics
$name = $pdu->getOutletStatus(1, 5, OutletMetric::Name);
$power = $pdu->getOutletStatus(1, 5, OutletMetric::Power);
$current = $pdu->getOutletStatus(1, 5, OutletMetric::Current);
$energy = $pdu->getOutletStatus(1, 5, OutletMetric::Energy);

// Get all metrics for one outlet
$outlet = $pdu->getOutletAll(1, 5);
// Returns: ['name' => 'Server1', 'current_a' => 1.2, 'power_w' => 150, ...]

// Get all outlets for a PDU
$outlets = $pdu->getAllOutlets(1);
```

### Complete Status

```php
$status = $pdu->getFullStatus();
// Returns complete dump of all PDUs and outlets
```

### Network Port Sharing (NPS)

When multiple PDUs are daisy-chained (up to 4 PDUs supported), specify the PDU index as the second parameter:

```php
use Sunfox\ApcPdu\DeviceMetric;

// PDU 1 (Host) - default when no index specified
$hostPower = $pdu->getDevice(DeviceMetric::Power);
$hostPower = $pdu->getDevice(DeviceMetric::Power, 1);  // Explicit

// PDU 2 (Guest)
$guest1Power = $pdu->getDevice(DeviceMetric::Power, 2);

// PDU 3 and 4 (additional guests)
$guest2Power = $pdu->getDevice(DeviceMetric::Power, 3);
$guest3Power = $pdu->getDevice(DeviceMetric::Power, 4);

// Get all metrics for specific PDU
$device = $pdu->getDeviceAll(2);  // All metrics for PDU 2
```

### Testing Connection

```php
if ($pdu->testConnection()) {
    echo "PDU is reachable";
}
```

## Available Metrics

### Device Metrics (DeviceMetric)

| Metric | Unit | Description |
|--------|------|-------------|
| Power | W | Current power consumption |
| PeakPower | W | Peak power since last reset |
| Energy | kWh | Total energy since last reset |

### Outlet Metrics (OutletMetric)

| Metric | Unit | Description |
|--------|------|-------------|
| Name | string | Outlet name/label |
| Index | int | SNMP index |
| Current | A | Current draw |
| Power | W | Power consumption |
| PeakPower | W | Peak power |
| Energy | kWh | Total energy |

## Tested Devices

- APC AP8653 (Metered by Outlet with Switching)
- Rack PDU FW: 7.1.4
- APC OS: 7.1.2

## Development

### Running Tests with Docker

```bash
# Create .env file with your PDU credentials
cp .env.example .env
# Edit .env with your settings

# Run all tests
UID=$(id -u) GID=$(id -g) docker compose run --rm php

# Run only unit tests
UID=$(id -u) GID=$(id -g) docker compose run --rm unit

# Run only integration tests (requires real PDU)
UID=$(id -u) GID=$(id -g) docker compose run --rm integration

# Run PHPStan analysis
UID=$(id -u) GID=$(id -g) docker compose run --rm phpstan

# Run PSR-12 coding style check
UID=$(id -u) GID=$(id -g) docker compose run --rm phpcs

# Auto-fix PSR-12 violations
UID=$(id -u) GID=$(id -g) docker compose run --rm phpcbf

# Interactive shell
UID=$(id -u) GID=$(id -g) docker compose run --rm shell
```

### Without Docker

```bash
composer install
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for details.
