# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

MikoPBX module that integrates Zabbix Agent (built from source as v6.0.44) for monitoring Asterisk PBX metrics. Collects active calls, channels, SIP peer/trunk status, uptime, and version info via `asterisk[<metric>]` UserParameter keys.

- **Module ID:** ModuleZabbixAgent5
- **Namespace:** `Modules\ModuleZabbixAgent5`
- **Min PBX version:** 2025.1.1
- **PHP:** 8.4 with Phalcon 5.x
- **License:** GPL-3.0-or-later

## Build & Development

**JS compilation** (source → compiled with inline sourcemaps):
```bash
/Users/nb/PhpstormProjects/mikopbx/MikoPBXUtils/node_modules/.bin/babel \
  public/assets/js/src/module-zabbix-agent5-index.js \
  --out-dir public/assets/js \
  --source-maps inline \
  --presets airbnb
```

**PHP quality check** — no test suite; use `phpstan` after PHP changes.

**CI** (`.github/workflows/build.yml`): Inherits from `mikopbx/.github-workflows` shared workflow. Compiles Zabbix Agent 6.0.44 statically for amd64 (Dockerfile.amd64) and arm64 (Dockerfile.arm64, cross-compiled), placing binaries at `bin/zabbix_agentd` and `bin/zabbix_agentd_arm`. Build Dockerfiles live in `.github/build/zabbix-agent-builder/`. Triggers on push to `master` and `develop`.

## Architecture

### Data Flow

1. **Web UI** → User edits `zabbix_agentd.conf` in ACE editor (Volt template + JS)
2. **Controller** (`saveAction`) → saves `configContent` to `m_ModuleZabbixAgent5` table
3. **Config hook** (`modelsEventChangeData`) detects DB change → calls `ZabbixAgent5Main::startService()`
4. **Service manager** writes config to `/etc/custom_modules/ModuleZabbixAgent5/zabbix_agentd.conf` and restarts `zabbix_agentd`
5. **Zabbix agent** calls `bin/asterisk-stats.sh <function>` via UserParameter — shell functions for simple metrics (uptime, version, calls via CLI), delegates to `php -f Lib/AsteriskInfo.php <method>` for API-based metrics (SIP peers, trunks, call direction counts)
6. **Cron** runs `bin/zabbix-safe-script.sh` every 5 min as a health check to restart agent if not running

### Key Extension Points (MikoPBX Module System)

- **`Lib/ZabbixAgent5Conf.php`** (extends `ConfigClass`) — lifecycle hooks: start/stop service on enable/disable, restart on DB change, firewall rules (port 10050), cron task registration
- **`Lib/ZabbixAgent5Main.php`** — core service manager: directory setup, config loading (DB → fallback to `bin/zabbix_agentd_default.conf`), start/stop via `Processes::processWorker`
- **`Lib/AsteriskInfo.php`** — standalone CLI script (not autoloaded) that collects Asterisk metrics via MikoPBX REST API v3 (`/pbxcore/api/v3/pbx-status:getActiveCalls`, `/pbxcore/api/v3/sip:getStatuses`, `/pbxcore/api/v3/sip-providers:getStatuses`). Invoked as `php -f AsteriskInfo.php <methodName> [args]` by shell scripts
- **`Lib/MikoPBXVersion.php`** — Phalcon 5 helper (Di, Validation, Logger class resolution)
- **`Setup/PbxExtensionSetup.php`** — empty, inherits all default install/uninstall behavior

### Workers (Beanstalk/AMI)

- **`WorkerZabbixAgent5Main.php`** — Beanstalk message queue listener
- **`WorkerZabbixAgent5AMI.php`** — Asterisk Manager Interface event listener (filters `Interception` UserEvent)

Both workers require `Globals.php` (via `require_once`) and are started as CLI processes with `cli_set_process_title`.

### MVC Layer

- **Model:** `Models/ModuleZabbixAgent5.php` — table `m_ModuleZabbixAgent5`, fields: `id`, `configContent` (LONGTEXT)
- **Controller:** `App/Controllers/ModuleZabbixAgent5Controller.php` — `indexAction` renders ACE editor, `saveAction` handles POST
- **Form:** `App/Forms/ModuleZabbixAgent5Form.php` — two hidden fields (`id`, `configContent`)
- **View:** `App/Views/index.volt` — Volt template, uses ACE editor with `julia` syntax mode for config highlighting
- **JS:** `public/assets/js/src/module-zabbix-agent5-index.js` — initializes ACE editor (monokai theme), hooks into MikoPBX `Form` object for save

### Runtime Paths (on MikoPBX)

- Config: `/etc/custom_modules/ModuleZabbixAgent5/zabbix_agentd.conf`
- Logs: `/storage/usbdisk1/mikopbx/log/ModuleZabbixAgent5/`
- PID: `/var/run/custom_modules/ModuleZabbixAgent5/`

## Conventions

- PSR-4 autoloading from root: `Modules\ModuleZabbixAgent5\` → `/`
- JS source in `public/assets/js/src/`, compiled output in `public/assets/js/`
- 29 translation files in `Messages/` — PHP arrays with `modzbx_` prefix keys; add translations to all locale files when adding new strings
- Shell scripts in `bin/` must use busybox-compatible syntax (MikoPBX runs on a minimal Linux — no bash, use `/bin/sh` with `/bin/busybox` commands)
- `AsteriskInfo.php` is a CLI-invoked script with `require_once 'Globals.php'` — it bootstraps outside the MikoPBX autoloader
- Zabbix agent binaries (`bin/zabbix_agentd`, `bin/zabbix_agentd_arm`) are gitignored — built by CI only
