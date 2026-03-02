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

## License

GPL-3.0-or-later

---

# ModuleZabbixAgent5 (Русский)

Модуль интеграции Zabbix Agent с MikoPBX. Собирает и отправляет метрики АТС на сервер Zabbix для мониторинга.

## Возможности

- Zabbix Agent 6.0 LTS (v6.0.44), статически слинкован (без внешних зависимостей)
- Поддержка архитектур x86_64 и ARM64 (aarch64)
- Веб-интерфейс для редактирования `zabbix_agentd.conf`
- Отображение статуса сервиса в реальном времени (состояние, PID, версия, порт, сервер)
- Кнопка скачивания шаблона Zabbix для импорта на сервер Zabbix
- REST API v3 с автообнаружением (PHP 8 атрибуты, паттерн Processor + Actions)
- Автоматическое управление сервисом (старт/стоп/рестарт)
- Правило файрвола для порта Zabbix (по умолчанию 10050)
- Проверка состояния сервиса по cron каждые 5 минут

## Метрики мониторинга

Все метрики доступны через UserParameter ключ `asterisk[<функция>]`.

### Метрики Asterisk

| Метрика | Ключ Zabbix |
|---|---|
| Активные вызовы (Asterisk CLI) | `asterisk[callsActive]` |
| Обработанные вызовы | `asterisk[callsProcessed]` |
| Активные каналы | `asterisk[channelsActive]` |
| Входящие вызовы (через API) | `asterisk[countInCalls]` |
| Исходящие вызовы (через API) | `asterisk[countOutCalls]` |
| Внутренние вызовы (через API) | `asterisk[countInnerCalls]` |
| SIP-пиры (всего) | `asterisk[countSipPeers]` |
| SIP-пиры (онлайн) | `asterisk[CountActivePeers]` |
| SIP-транки (онлайн) | `asterisk[CountActiveProviders]` |
| SIP-транки (офлайн) | `asterisk[CountNonActiveProviders]` |
| Аптайм Asterisk | `asterisk[statusUptime]` |
| Время последнего reload | `asterisk[statusReload]` |
| Версия Asterisk | `asterisk[version]` |
| Статус Asterisk (1/0) | `asterisk[status]` |

### Обнаружение SIP-транков (LLD)

| Метрика | Ключ Zabbix |
|---|---|
| Статус регистрации транка | `asterisk[trunkStatus,{#TRUNKID}]` |
| Входящие звонки за час | `asterisk[trunkCalls,{#TRUNKID},hour,incoming,totalCalls]` |
| Исходящие звонки за час | `asterisk[trunkCalls,{#TRUNKID},hour,outgoing,totalCalls]` |
| Входящие звонки за сутки | `asterisk[trunkCalls,{#TRUNKID},day,incoming,totalCalls]` |
| Исходящие звонки за сутки | `asterisk[trunkCalls,{#TRUNKID},day,outgoing,totalCalls]` |
| Отвеченные входящие за час | `asterisk[trunkCalls,{#TRUNKID},hour,incoming,answeredCalls]` |
| Отвеченные исходящие за час | `asterisk[trunkCalls,{#TRUNKID},hour,outgoing,answeredCalls]` |

### Мониторинг диска /storage

| Метрика | Ключ Zabbix |
|---|---|
| Общий размер | `vfs.fs.size[/storage,total]` |
| Занято | `vfs.fs.size[/storage,used]` |
| Свободно | `vfs.fs.size[/storage,free]` |
| Свободно (%) | `vfs.fs.size[/storage,pfree]` |

## REST API

REST API v3 эндпоинты (автообнаружение через PHP 8 атрибуты):

| Метод | Эндпоинт | Описание |
|---|---|---|
| GET | `/pbxcore/api/v3/module-zabbix-agent5/status:getStatus` | Статус сервиса (запущен, PID, версия, порт, сервер) |
| GET | `/pbxcore/api/v3/module-zabbix-agent5/status:downloadTemplate` | Скачать шаблон Zabbix в формате YAML |

## Шаблон Zabbix

Включённый шаблон (`bin/zbx_export_templates.yaml`) содержит:
- Все элементы данных Asterisk
- LLD SIP-транков со статусом и статистикой звонков по каждому транку
- Мониторинг диска /storage с триггерами (WARNING <10%, HIGH <5%)
- Графики: обзор звонков, SIP-эндпоинты, звонки по транкам за час, использование хранилища
- Триггеры: Asterisk не запущен, неактивные транки, нет SIP-пиров, нет данных от агента

## Сборка

Бинарники собираются автоматически в GitHub Actions:
- **AMD64**: нативная сборка на Alpine Linux с musl (статическая линковка)
- **ARM64**: кросс-компиляция на AMD64 с помощью `aarch64-linux-gnu-gcc`

Оба варианта — полностью статически слинкованные бинарники с поддержкой OpenSSL.

## Требования

- MikoPBX 2025.1.1+

## Лицензия

GPL-3.0-or-later
