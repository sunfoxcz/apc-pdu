# APC PDU Library

PHP library for reading data from APC PDU AP8XXX series via SNMP (v1 and v3) or SSH with Network Port Sharing support.

## Requirements

- PHP 8.2+
- One of the following backends:
  - `ext-snmp` - PHP SNMP extension (for Native client)
  - `net-snmp` - System package (for Binary client)
  - `freedsx/snmp` - Composer package (for FreeDSx client)
  - `ext-ssh2` - PHP SSH2 extension (for SSH client)

## Installation

```bash
composer require sunfox/apc-pdu
```

## Usage

### SNMPv1 Connection

```php
use Sunfox\ApcPdu\ApcPduFactory;
use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;

$pdu = ApcPduFactory::snmpV1('192.168.1.100', 'public');
```

### SNMPv3 Connection

```php
$pdu = ApcPduFactory::snmpV3(
    '192.168.1.100',
    'monitor',
    'AuthPassphrase',
    'PrivPassphrase'
);
```

### Client Implementations

The library provides multiple SNMP client implementations with automatic discovery:

| Method | Description | Dependencies |
|--------|-------------|--------------|
| `snmpV1()` / `snmpV3()` | **Auto-detects** best available client | Any of the below |
| `snmpV1Binary()` / `snmpV3Binary()` | Uses the `snmpget` binary via shell | `net-snmp` package |
| `snmpV1FreeDsx()` / `snmpV3FreeDsx()` | Uses the FreeDSx SNMP library | `freedsx/snmp` composer package |
| `snmpV1Native()` / `snmpV3Native()` | Uses PHP's native SNMP functions | `ext-snmp` |
| `ssh()` | Uses SSH/CLI interface | `ext-ssh2` |

#### Automatic Client Discovery

The `snmpV1()` and `snmpV3()` methods automatically detect and use the best available SNMP client in priority order:

1. **Binary** - Most efficient batch operations (`net-snmp` package)
2. **FreeDSx** - Efficient batch operations, pure PHP (`freedsx/snmp`)
3. **Native** - Fallback, slower batch operations (`ext-snmp`)

If no client is available, a `NoSnmpClientAvailableException` is thrown.

```php
// Auto-detect best available client (recommended)
$pdu = ApcPduFactory::snmpV3($host, $user, $authPass, $privPass);

// Or explicitly choose a specific client:

// Binary - requires net-snmp package (apt install snmp)
$pdu = ApcPduFactory::snmpV3Binary($host, $user, $authPass, $privPass);

// FreeDSx - pure PHP, no extensions required (composer require freedsx/snmp)
$pdu = ApcPduFactory::snmpV3FreeDsx($host, $user, $authPass, $privPass);

// Native - requires ext-snmp PHP extension
$pdu = ApcPduFactory::snmpV3Native($host, $user, $authPass, $privPass);

// SSH - uses CLI commands over SSH (requires ext-ssh2)
$pdu = ApcPduFactory::ssh($host, $sshUser, $sshPass);
```

### Performance Comparison

Benchmark results (SNMPv3, AP8653 PDU):

| Operation | Native | Binary | FreeDSx | SSH |
|-----------|--------|--------|---------|-----|
| Single device metric | 128 ms | 69 ms | 73 ms | 905 ms |
| Device metrics (batch) | 1.26 s | 200 ms | 215 ms | N/A* |
| Single outlet metric | 62 ms | 65 ms | 233 ms | 904 ms |
| Outlet metrics (batch) | 1.09 s | 213 ms | 229 ms | N/A* |
| All 24 outlets | 25.82 s | 7.07 s | 7.45 s | N/A* |
| Full PDU status | 54.74 s | 16.64 s | 13.72 s | N/A* |

*SSH uses an interactive shell which adds latency. It supports only a limited set of metrics (Power, Energy, ApparentPower, PowerFactor, Current, Name) and cannot retrieve full device/outlet status.

**Recommendations:**
- **Native**: Good for simple single-metric queries, requires `ext-snmp`
- **Binary**: Best batch performance, requires `net-snmp` system package
- **FreeDSx**: Best for full status dumps, pure PHP with no extension dependencies
- **SSH**: Alternative when SNMP is unavailable, limited metrics support

Run `bin/benchmark` to compare performance on your system. Use `--ssh` flag to include SSH in the comparison.

### Device-Level Metrics

```php
// Get individual metrics (PDU 1 is default)
$power = $pdu->getDevice(DeviceMetric::Power);           // Returns watts
$peak = $pdu->getDevice(DeviceMetric::PeakPower);        // Returns watts
$energy = $pdu->getDevice(DeviceMetric::Energy);         // Returns kWh
$name = $pdu->getDevice(DeviceMetric::Name);             // Returns string
$loadStatus = $pdu->getDevice(DeviceMetric::LoadStatus); // Returns int (1=Normal, 2=LowLoad, 3=NearOverload, 4=Overload)
$apparentPower = $pdu->getDevice(DeviceMetric::ApparentPower);  // Returns VA
$powerFactor = $pdu->getDevice(DeviceMetric::PowerFactor);      // Returns ratio (0.0-1.0)

// Get all device metrics at once as DTO
$device = $pdu->getDeviceStatus();
echo $device->powerW;           // Current power in watts
echo $device->peakPowerW;       // Peak power in watts
echo $device->energyKwh;        // Total energy in kWh
echo $device->name;             // Device name
echo $device->loadStatus->name; // LoadStatus enum (Normal, LowLoad, NearOverload, Overload)
echo $device->apparentPowerVa;  // Apparent power in VA
echo $device->powerFactor;      // Power factor (0.0-1.0)
echo $device->outletCount;      // Number of outlets
echo $device->phaseCount;       // Number of phases
```

### Outlet-Level Metrics

```php
// Get individual outlet metrics
$name = $pdu->getOutlet(1, 5, OutletMetric::Name);
$power = $pdu->getOutlet(1, 5, OutletMetric::Power);
$current = $pdu->getOutlet(1, 5, OutletMetric::Current);
$energy = $pdu->getOutlet(1, 5, OutletMetric::Energy);
$state = $pdu->getOutlet(1, 5, OutletMetric::State);  // 1=Off, 2=On

// Get all metrics for one outlet as DTO
$outlet = $pdu->getOutletStatus(1, 5);
echo $outlet->name;          // Outlet name
echo $outlet->index;         // Outlet index
echo $outlet->state->name;   // PowerState enum (Off, On)
echo $outlet->currentA;      // Current in amps
echo $outlet->powerW;        // Power in watts
echo $outlet->peakPowerW;    // Peak power in watts
echo $outlet->energyKwh;     // Energy in kWh
echo $outlet->outletType;    // Outlet type (e.g., "IEC C13")

// Get all outlets for a PDU
$outlets = $pdu->getAllOutlets(1);
```

### Complete Status

```php
// Get complete status for one PDU
$pduInfo = $pdu->getPduInfo(1);
echo $pduInfo->pduIndex;
echo $pduInfo->device->powerW;
foreach ($pduInfo->outlets as $outlet) {
    echo $outlet->name . ': ' . $outlet->powerW . 'W';
}

// Get complete dump of all PDUs (stops when PDU not found)
$status = $pdu->getFullStatus();
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
$device = $pdu->getDeviceStatus(2);  // All metrics for PDU 2
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
| ModuleIndex | - | Module index |
| PduIndex | - | PDU index |
| Name | string | Device name |
| LoadStatus | LoadStatus | Load status (Normal, LowLoad, NearOverload, Overload) |
| Power | W | Current power consumption |
| PeakPower | W | Peak power since last reset |
| PeakPowerTimestamp | datetime | When peak power occurred |
| EnergyResetTimestamp | datetime | When energy counter was reset |
| Energy | kWh | Total energy since last reset |
| EnergyStartTimestamp | datetime | When energy counting started |
| ApparentPower | VA | Apparent power |
| PowerFactor | ratio | Power factor (0.0-1.0) |
| OutletCount | - | Number of outlets |
| PhaseCount | - | Number of phases |
| PeakPowerResetTimestamp | datetime | When peak power was reset |
| LowLoadThreshold | % | Low load warning threshold |
| NearOverloadThreshold | % | Near overload warning threshold |
| OverloadRestriction | - | Overload restriction setting |

### Outlet Metrics (OutletMetric)

| Metric | Unit | Description |
|--------|------|-------------|
| ModuleIndex | - | Module index |
| PduIndex | - | PDU index |
| Name | string | Outlet name/label |
| Index | int | Outlet index |
| State | PowerState | Power state (Off, On) |
| Current | A | Current draw |
| Power | W | Power consumption |
| PeakPower | W | Peak power |
| PeakPowerTimestamp | datetime | When peak power occurred |
| EnergyResetTimestamp | datetime | When energy counter was reset |
| Energy | kWh | Total energy |
| OutletType | string | Outlet type (e.g., "IEC C13") |
| ExternalLink | string | External link URL |

### Enums

```php
use Sunfox\ApcPdu\LoadStatus;
use Sunfox\ApcPdu\PowerState;

// LoadStatus values
LoadStatus::Normal;       // 1
LoadStatus::LowLoad;      // 2
LoadStatus::NearOverload; // 3
LoadStatus::Overload;     // 4

// PowerState values
PowerState::Off; // 1
PowerState::On;  // 2
```

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

# Run unit tests (default)
docker compose run --rm php

# Run integration tests (requires real PDU)
docker compose run --rm integration

# Run PHPStan analysis
docker compose run --rm phpstan

# Run PSR-12 coding style check
docker compose run --rm phpcs

# Auto-fix PSR-12 violations
docker compose run --rm phpcbf

# Run benchmark (requires real PDU)
docker compose run --rm php bin/benchmark --help
```

### Without Docker

```bash
composer install
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for details.
