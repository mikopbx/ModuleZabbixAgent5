[![CI](https://github.com/mikopbx/ModuleZabbixAgent5/actions/workflows/build.yml/badge.svg)](https://github.com/mikopbx/ModuleZabbixAgent5/actions/workflows/build.yml) [![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0) [![GitHub Release](https://img.shields.io/github/v/release/mikopbx/ModuleZabbixAgent5)](https://github.com/mikopbx/ModuleZabbixAgent5/releases) [![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4.svg)](https://www.php.net/) [![Zabbix 6.0](https://img.shields.io/badge/Zabbix-6.0_LTS-D40000.svg)](https://www.zabbix.com/) [![MikoPBX 2025.1.1+](https://img.shields.io/badge/MikoPBX-2025.1.1+-1DBF73.svg)](https://www.mikopbx.com/) [![Issues](https://img.shields.io/github/issues/mikopbx/ModuleZabbixAgent5)](https://github.com/mikopbx/ModuleZabbixAgent5/issues)

[English](README.md) | [Русский](README.ru.md)

# ModuleZabbixAgent5

Module for integrating Zabbix Agent with MikoPBX. Collects and sends PBX metrics to a Zabbix server for monitoring.

## Features

- Zabbix Agent 6.0 LTS (v6.0.44), statically linked (no external dependencies)
- Supports x86_64 and ARM64 (aarch64) architectures
- Web interface for editing `zabbix_agentd.conf`
- Real-time service status display (running state, PID, version, port, server)
- Download Zabbix template button for easy import into Zabbix server
- REST API v3 with auto-discovery (PHP 8 attributes, Processor + Actions pattern)
- Automatic service management (start/stop/restart)
- Firewall rule for Zabbix port (default 10050)
- Cron-based health check every 5 minutes

## Installation

### From the MikoPBX Marketplace

1. Open the MikoPBX web interface.
2. Navigate to **Modules** > **Marketplace**.
3. Find **ModuleZabbixAgent5** in the list and click **Install**.
4. Once installed, enable the module on the **Modules** > **Installed** page.

### Manual installation

1. Download the latest `.zip` release from the [Releases](https://github.com/mikopbx/ModuleZabbixAgent5/releases) page.
2. In the MikoPBX web interface, go to **Modules** > **Installed**.
3. Click **Upload module** and select the downloaded `.zip` file.
4. Enable the module after installation.

## Configuration

After enabling the module, open its settings page in MikoPBX to edit the Zabbix agent configuration.

### Connecting to a Zabbix server

Set the following directives in `zabbix_agentd.conf` via the built-in editor:

- **`Server`** -- comma-separated list of Zabbix server/proxy IP addresses allowed to query the agent (passive checks).
- **`ServerActive`** -- Zabbix server/proxy address for active checks (the agent initiates the connection). Format: `<address>:<port>`.

Example:

```
Server=192.168.1.100
ServerActive=192.168.1.100:10051
Hostname=mikopbx-office
```

### Importing the Zabbix template

1. In the module settings page, click **Download Zabbix Template** to save the YAML file.
   Alternatively, download it via the REST API: `GET /pbxcore/api/v3/module-zabbix-agent5/status:downloadTemplate`.
2. Open your Zabbix server web interface.
3. Navigate to **Data collection** > **Templates** (Zabbix 6.0+) or **Configuration** > **Templates** (older versions).
4. Click **Import** and upload the downloaded `zbx_export_templates.yaml` file.
5. Assign the imported template to the host representing your MikoPBX instance.

## Monitored metrics

All metrics are accessed via `asterisk[<function>]` UserParameter key.

### Asterisk metrics

| Metric | Zabbix item key |
|---|---|
| Active calls (Asterisk CLI) | `asterisk[callsActive]` |
| Processed calls | `asterisk[callsProcessed]` |
| Active channels | `asterisk[channelsActive]` |
| Incoming calls (via API) | `asterisk[countInCalls]` |
| Outgoing calls (via API) | `asterisk[countOutCalls]` |
| Internal calls (via API) | `asterisk[countInnerCalls]` |
| SIP peers (total) | `asterisk[countSipPeers]` |
| SIP peers (online) | `asterisk[CountActivePeers]` |
| SIP trunks (online) | `asterisk[CountActiveProviders]` |
| SIP trunks (offline) | `asterisk[CountNonActiveProviders]` |
| Asterisk uptime | `asterisk[statusUptime]` |
| Last reload time | `asterisk[statusReload]` |
| Asterisk version | `asterisk[version]` |
| Asterisk status (1/0) | `asterisk[status]` |

### SIP trunk discovery (LLD)

| Metric | Zabbix item key |
|---|---|
| Trunk registration status | `asterisk[trunkStatus,{#TRUNKID}]` |
| Incoming calls per hour | `asterisk[trunkCalls,{#TRUNKID},hour,incoming,totalCalls]` |
| Outgoing calls per hour | `asterisk[trunkCalls,{#TRUNKID},hour,outgoing,totalCalls]` |
| Incoming calls per day | `asterisk[trunkCalls,{#TRUNKID},day,incoming,totalCalls]` |
| Outgoing calls per day | `asterisk[trunkCalls,{#TRUNKID},day,outgoing,totalCalls]` |
| Answered incoming per hour | `asterisk[trunkCalls,{#TRUNKID},hour,incoming,answeredCalls]` |
| Answered outgoing per hour | `asterisk[trunkCalls,{#TRUNKID},hour,outgoing,answeredCalls]` |

### Storage disk monitoring

| Metric | Zabbix item key |
|---|---|
| Total size | `vfs.fs.size[/storage,total]` |
| Used space | `vfs.fs.size[/storage,used]` |
| Free space | `vfs.fs.size[/storage,free]` |
| Free space (%) | `vfs.fs.size[/storage,pfree]` |

## REST API

REST API v3 endpoints (auto-discovered via PHP 8 attributes):

| Method | Endpoint | Description |
|---|---|---|
| GET | `/pbxcore/api/v3/module-zabbix-agent5/status:getStatus` | Service status (running, pid, version, port, server) |
| GET | `/pbxcore/api/v3/module-zabbix-agent5/status:downloadTemplate` | Download Zabbix template YAML |

## Zabbix template

Included template (`bin/zbx_export_templates.yaml`) provides:
- All Asterisk metric items
- SIP trunk LLD with per-trunk status and call statistics
- Storage disk monitoring items with triggers (WARNING <10%, HIGH <5%)
- Graphs: Calls overview, SIP endpoints, Trunk calls per hour, Storage usage
- Triggers: Asterisk not running, non-active trunks, no SIP peers, no data from agent

## Build

Binaries are built automatically in GitHub Actions:
- **AMD64**: native Alpine Linux build with musl (static linking)
- **ARM64**: cross-compile on AMD64 using `aarch64-linux-gnu-gcc`

Both produce fully statically linked binaries with OpenSSL support.

## Requirements

- MikoPBX 2025.1.1+
- PHP 8.4
- Zabbix Server 5.0+ (template compatible with 5.0, 6.0, 7.0)

## Support

- **Issues**: [GitHub Issues](https://github.com/mikopbx/ModuleZabbixAgent5/issues)
- **Wiki**: [GitHub Wiki](https://github.com/mikopbx/ModuleZabbixAgent5/wiki)
- **Email**: [help@miko.ru](mailto:help@miko.ru)
- **Telegram**: [@mikaboris](https://t.me/mikaboris)

## License

GPL-3.0-or-later
