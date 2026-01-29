# APC PDU AP8XXX Series OID Reference

This document covers SNMP OIDs for APC Rack PDU AP8XXX series (including AP8653).

## OID Tree Structure

All AP8XXX PDU OIDs are under the rPDU2 branch:
```
.iso.org.dod.internet.private.enterprises.apc.products.hardware.rPDU2
.1.3.6.1.4.1.318.1.1.26
```

## Device Configuration OIDs

Base: `.1.3.6.1.4.1.318.1.1.26.4.1.1.{metric}.{pdu_index}`

| Suffix | Name | Type | Access | Description |
|--------|------|------|--------|-------------|
| .1 | rPDU2DeviceConfigIndex | INTEGER | RO | Index to the Rack PDU table entry |
| .2 | rPDU2DeviceConfigModule | INTEGER | RW | User-defined Rack PDU numeric ID |
| .3 | rPDU2DeviceConfigName | STRING | RW | User-defined string identifying the Rack PDU |
| .4 | rPDU2DeviceConfigLocation | STRING | RW | User-defined location text |
| .5 | rPDU2DeviceConfigDisplayOrientation | INTEGER | RW | Seven-segment display orientation |
| .6 | rPDU2DeviceConfigColdstartDelay | INTEGER | RW | Delay between power provision and PDU operation |
| .7 | rPDU2DeviceConfigLowLoadPowerThreshold | INTEGER | RW | Low power draw alarm threshold |
| .8 | rPDU2DeviceConfigNearOverloadPowerThreshold | INTEGER | RW | Near-overload power threshold |
| .9 | rPDU2DeviceConfigOverloadPowerThreshold | INTEGER | RW | Overload power threshold |
| .10 | rPDU2DeviceConfigDevicePeakPowerReset | INTEGER | RW | Reset device peak power (set to 2) |
| .11 | rPDU2DeviceConfigDeviceEnergyReset | INTEGER | RW | Reset device energy meter (set to 2) |
| .12 | rPDU2DeviceConfigOutletsEnergyReset | INTEGER | RW | Reset all outlet energy meters (set to 2) |
| .13 | rPDU2DeviceConfigOutletsPeakLoadReset | INTEGER | RW | Reset all outlet peak power values (set to 2) |

### Reset OID Values

| Value | Meaning |
|-------|---------|
| 1 | noOperation (returned when reading) |
| 2 | reset (set this value to trigger reset) |
| 3 | notSupported (model doesn't support this feature) |

## Device Status OIDs

Base: `.1.3.6.1.4.1.318.1.1.26.4.3.1.{metric}.{pdu_index}`

| Suffix | Name | Units | Conversion | Description |
|--------|------|-------|------------|-------------|
| .1 | rPDU2DeviceStatusIndex | - | - | Index to status table entry |
| .2 | rPDU2DeviceStatusModule | - | - | PDU numeric ID (module index) |
| .3 | rPDU2DeviceStatusName | STRING | - | PDU name |
| .4 | rPDU2DeviceStatusLoadState | INTEGER | - | Load status (1=low, 2=normal, 3=nearOverload, 4=overload) |
| .5 | rPDU2DeviceStatusPower | hundredths kW | ×10 → W | Current power consumption |
| .6 | rPDU2DeviceStatusPeakPower | hundredths kW | ×10 → W | Peak power consumption |
| .7 | rPDU2DeviceStatusPeakPowerTimestamp | STRING | - | When peak power occurred |
| .8 | rPDU2DeviceStatusPeakPowerStartTime | STRING | - | Last peak power reset timestamp |
| .9 | rPDU2DeviceStatusEnergy | tenths kWh | ÷10 → kWh | Accumulated energy consumption |
| .10 | rPDU2DeviceStatusEnergyStartTime | STRING | - | Last energy meter reset timestamp |
| .11 | rPDU2DeviceStatusCommandPending | INTEGER | - | Command processing status |
| .12 | rPDU2DeviceStatusPowerSupplyAlarm | INTEGER | - | Power supply alarm (1=normal, 2=alarm) |
| .13 | rPDU2DeviceStatusPowerSupply1Status | INTEGER | - | PS1 status |
| .14 | rPDU2DeviceStatusPowerSupply2Status | INTEGER | - | PS2 status |
| .15 | rPDU2DeviceStatusOutletsEnergyStartTime | STRING | - | Last outlets energy reset timestamp |
| .16 | rPDU2DeviceStatusApparentPower | hundredths kVA | ×10 → VA | Apparent power |
| .17 | rPDU2DeviceStatusPowerFactor | hundredths | ÷100 → ratio | Power factor (0.00-1.00) |
| .18 | rPDU2DeviceStatusNPSType | INTEGER | - | NPS group status |

## Outlet Status OIDs (Metered)

Base: `.1.3.6.1.4.1.318.1.1.26.9.4.3.1.{metric}.{snmp_index}`

| Suffix | Name | Units | Conversion | Description |
|--------|------|-------|------------|-------------|
| .1 | rPDU2OutletMeteredStatusIndex | INTEGER | - | SNMP index |
| .2 | rPDU2OutletMeteredStatusModule | INTEGER | - | PDU module index |
| .3 | rPDU2OutletMeteredStatusName | STRING | - | Outlet name |
| .4 | rPDU2OutletMeteredStatusNumber | INTEGER | - | Outlet number on PDU |
| .5 | rPDU2OutletMeteredStatusState | INTEGER | - | **Always returns 2 (on) - use Switched Status for actual state** |
| .6 | rPDU2OutletMeteredStatusCurrent | tenths A | ÷10 → A | Current draw |
| .7 | rPDU2OutletMeteredStatusPower | W | direct | Power consumption |
| .8 | rPDU2OutletMeteredStatusPeakPower | W | direct | Peak power |
| .9 | rPDU2OutletMeteredStatusPeakPowerTimestamp | STRING | - | When peak power occurred |
| .10 | rPDU2OutletMeteredStatusPeakPowerStartTime | STRING | - | Peak power observation start |
| .11 | rPDU2OutletMeteredStatusEnergy | tenths kWh | ÷10 → kWh | Energy consumption |
| .12 | rPDU2OutletMeteredStatusOutletType | STRING | - | Outlet type (e.g., "IEC C13") |
| .13 | rPDU2OutletMeteredStatusExternalLink | STRING | - | External URL link |

> **Note:** The `rPDU2OutletMeteredStatusState` (suffix .5) always returns 2 (on) regardless of actual
> outlet switch state. To get the actual on/off state, use the Switched Status table below.

## Outlet Status OIDs (Switched)

Base: `.1.3.6.1.4.1.318.1.1.26.9.2.3.1.{metric}.{snmp_index}`

| Suffix | Name | Type | Description |
|--------|------|------|-------------|
| .1 | rPDU2OutletSwitchedStatusIndex | INTEGER | SNMP index |
| .2 | rPDU2OutletSwitchedStatusModule | INTEGER | PDU module index |
| .3 | rPDU2OutletSwitchedStatusName | STRING | Outlet name |
| .4 | rPDU2OutletSwitchedStatusNumber | INTEGER | Outlet number on PDU |
| .5 | rPDU2OutletSwitchedStatusState | INTEGER | **Actual power state (1=off, 2=on)** |
| .6 | rPDU2OutletSwitchedStatusCommandPending | INTEGER | Command pending status |
| .7 | rPDU2OutletSwitchedStatusExternalLink | STRING | External URL link |

## Outlet Configuration OIDs (Metered)

Base: `.1.3.6.1.4.1.318.1.1.26.9.4.1.1.{metric}.{snmp_index}`

| Suffix | Name | Type | Access | Description |
|--------|------|------|--------|-------------|
| .1 | rPDU2OutletMeteredConfigIndex | INTEGER | RO | Index to config table entry |
| .2 | rPDU2OutletMeteredConfigModule | INTEGER | RO | PDU module index |
| .3 | rPDU2OutletMeteredConfigName | STRING | RW | Outlet name (writable) |
| .4 | rPDU2OutletMeteredConfigNumber | INTEGER | RO | Outlet number |
| .5 | rPDU2OutletMeteredConfigLowLoadCurrentThreshold | INTEGER | RW | Low load threshold |
| .6 | rPDU2OutletMeteredConfigNearOverloadCurrentThreshold | INTEGER | RW | Near overload threshold |
| .7 | rPDU2OutletMeteredConfigOverloadCurrentThreshold | INTEGER | RW | Overload threshold |

## Outlet Switched Control OIDs

Base: `.1.3.6.1.4.1.318.1.1.26.9.2.4.1.{metric}.{snmp_index}`

| Suffix | Name | Type | Access | Description |
|--------|------|------|--------|-------------|
| .5 | rPDU2OutletSwitchedControlCommand | INTEGER | RW | Control outlet (1=off, 2=on, 3=reboot) |

## Network Port Sharing (NPS)

When PDUs are daisy-chained via Network Port Sharing:

- Host PDU: pdu_index = 1, outlet SNMP indices = 1-24
- Guest PDU 1: pdu_index = 2, outlet SNMP indices = 25-48
- Guest PDU 2: pdu_index = 3, outlet SNMP indices = 49-72
- Guest PDU 3: pdu_index = 4, outlet SNMP indices = 73-96

**Formula:** `snmp_index = ((pdu_index - 1) × outlets_per_pdu) + outlet_number`

## SNMP Types for SET Operations

| Type | Code | Description |
|------|------|-------------|
| INTEGER | i | Integer value |
| STRING | s | Octet string |

## Load Status Values

| Value | Name | Description |
|-------|------|-------------|
| 1 | lowLoad | Below low load threshold |
| 2 | normal | Normal operation |
| 3 | nearOverload | Approaching overload |
| 4 | overload | Over load threshold |

## Power State Values

| Value | Name | Description |
|-------|------|-------------|
| 1 | off | Outlet is off |
| 2 | on | Outlet is on |

## Switched Control Commands

| Value | Name | Description |
|-------|------|-------------|
| 1 | immediateOff | Turn off immediately |
| 2 | immediateOn | Turn on immediately |
| 3 | immediateReboot | Reboot (off then on) |
| 4 | delayedOff | Turn off after delay |
| 5 | delayedOn | Turn on after delay |
| 6 | delayedReboot | Reboot after delay |
| 7 | cancelPendingCommand | Cancel pending command |

## References

- [Schneider Electric PowerNet MIB Download](https://www.se.com/us/en/download/document/APC_POWERNETMIB_EN/)
- [PowerNet MIB FAQ](https://www.se.com/us/en/faqs/FA156048/)
- [OIDref - rPDU2DeviceStatusPower](https://oidref.com/1.3.6.1.4.1.318.1.1.26.4.3.1.5)
