[![CI status](https://img.shields.io/github/actions/workflow/status/mikopbx/ModuleZabbixAgent5/build.yml?branch=master&label=CI)](https://github.com/mikopbx/ModuleZabbixAgent5/actions) [![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0) [![GitHub Release](https://img.shields.io/github/v/release/mikopbx/ModuleZabbixAgent5)](https://github.com/mikopbx/ModuleZabbixAgent5/releases) [![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4.svg)](https://www.php.net/) [![Zabbix 6.0](https://img.shields.io/badge/Zabbix-6.0-D40000.svg)](https://www.zabbix.com/) [![MikoPBX 2025.1.1+](https://img.shields.io/badge/MikoPBX-2025.1.1%2B-00AEEF.svg)](https://www.mikopbx.com/) [![GitHub Issues](https://img.shields.io/github/issues/mikopbx/ModuleZabbixAgent5)](https://github.com/mikopbx/ModuleZabbixAgent5/issues)

[English](README.md) | [Русский](README.ru.md)

# ModuleZabbixAgent5

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

## Установка

### Из маркетплейса MikoPBX

1. Откройте веб-интерфейс MikoPBX
2. Перейдите в **Module** -> **Marketplace**
3. Найдите "Zabbix" в строке поиска
4. Нажмите **Install**

### Ручная установка

1. Скачайте `.zip` архив из [GitHub Releases](https://github.com/mikopbx/ModuleZabbixAgent5/releases)
2. В веб-интерфейсе MikoPBX перейдите в **Module** -> **Install module from file**
3. Загрузите скачанный архив

## Настройка

1. После установки откройте модуль в веб-интерфейсе MikoPBX
2. В редакторе конфигурации укажите адрес вашего Zabbix-сервера:
   - `Server=<IP-адрес Zabbix-сервера>`
   - `ServerActive=<IP-адрес Zabbix-сервера>`
3. Сохраните конфигурацию -- сервис перезапустится автоматически
4. Импортируйте шаблон в Zabbix:
   - Скачайте шаблон через кнопку в веб-интерфейсе модуля
   - В Zabbix перейдите в **Configuration** -> **Templates** -> **Import**
   - Выберите скачанный YAML-файл и нажмите **Import**

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

Оба варианта -- полностью статически слинкованные бинарники с поддержкой OpenSSL.

## Поддержка

- [Issues на GitHub](https://github.com/mikopbx/ModuleZabbixAgent5/issues)
- [Документация](https://docs.mikopbx.com/mikopbx/modules/miko/module-zabbix-agent)
- Email: help@miko.ru
- Telegram: @mikaboris

## Требования

- MikoPBX 2025.1.1+

## Лицензия

GPL-3.0-or-later
