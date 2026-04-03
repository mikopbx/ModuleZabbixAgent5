#!/bin/sh

# Collects CDR statistics for Zabbix module and writes to cache files.
# Called by cron every 5 minutes to avoid thundering herd of API requests.

CACHE_DIR="/storage/usbdisk1/mikopbx/tmp/ModuleZabbixAgent5"
COLLECTOR="$(dirname "$0")/../Lib/StatsCollector.php"
LOCK_FILE="${CACHE_DIR}/collector.lock"

# Ensure cache directory exists
if [ ! -d "$CACHE_DIR" ]; then
    /bin/busybox mkdir -p "$CACHE_DIR"
fi

# Prevent overlapping runs via flock
exec 9>"$LOCK_FILE"
if ! /bin/busybox flock -n 9; then
    exit 0
fi

# Run collector
php -f "$COLLECTOR"
