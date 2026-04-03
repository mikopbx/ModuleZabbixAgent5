<?php
/*
 * MikoPBX - free phone system for small business
 * Copyright © 2017-2026 Alexey Portnov and Nikolay Beketov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace Modules\ModuleZabbixAgent5\Lib;

require_once 'Globals.php';
require_once __DIR__ . '/ApiHelper.php';

/**
 * Background CDR statistics collector for Zabbix module.
 *
 * Runs via cron every 5 minutes, collects trunk call statistics
 * from the REST API and writes pre-computed values to flat cache files.
 * This eliminates the thundering herd of parallel API requests
 * that Zabbix agent triggers when polling trunkCalls metrics.
 */
class StatsCollector
{
    public const CACHE_DIR = '/storage/usbdisk1/mikopbx/tmp/ModuleZabbixAgent5';

    private const PERIODS = ['hour', 'day'];
    private const DIRECTIONS = ['incoming', 'outgoing', 'all'];
    private const METRICS = ['totalCalls', 'answeredCalls', 'totalDuration', 'totalBillsec'];
    private const PROVIDER_ID_PATTERN = '/^[A-Za-z0-9_-]+$/';

    /**
     * Collects CDR statistics for all providers and writes cache files.
     */
    public static function collect(): void
    {
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }

        $providerIds = self::getProviderIds();

        // Clean up cache files for providers that no longer exist
        self::cleanupOrphanedFiles($providerIds);

        if (empty($providerIds)) {
            self::atomicWrite(self::CACHE_DIR . '/last_update', (string)time());
            return;
        }

        foreach ($providerIds as $providerId) {
            foreach (self::PERIODS as $period) {
                $now = new \DateTime();
                $dateTo = $now->format('Y-m-d H:i:s');
                $interval = $period === 'day' ? 'PT24H' : 'PT1H';
                $dateFrom = (clone $now)->sub(new \DateInterval($interval))->format('Y-m-d H:i:s');

                $data = ApiHelper::callApi('/pbxcore/api/v3/cdr:getStatsByProvider', [
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'provider' => $providerId,
                ]);

                self::writeCacheFiles($providerId, $period, $data);
            }
        }

        self::atomicWrite(self::CACHE_DIR . '/last_update', (string)time());
    }

    /**
     * Returns list of all provider IDs (SIP + IAX), validated for safe filenames.
     *
     * @return string[]
     */
    private static function getProviderIds(): array
    {
        $providers = ApiHelper::getAllProviderStatuses();
        $ids = [];
        foreach ($providers as $provider) {
            $id = $provider['id'] ?? '';
            if ($id !== '' && preg_match(self::PROVIDER_ID_PATTERN, $id)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    /**
     * Aggregates API response and writes one flat file per direction x metric combination.
     * Uses atomic write (temp file + rename) to prevent race conditions with Zabbix reads.
     */
    private static function writeCacheFiles(string $providerId, string $period, ?array $data): void
    {
        foreach (self::DIRECTIONS as $direction) {
            foreach (self::METRICS as $metric) {
                $value = 0;
                if (is_array($data)) {
                    foreach ($data as $row) {
                        if ($direction !== 'all' && ($row['direction'] ?? '') !== $direction) {
                            continue;
                        }
                        $value += (int)($row[$metric] ?? 0);
                    }
                }
                $filename = sprintf(
                    '%s/trunkCalls_%s_%s_%s_%s',
                    self::CACHE_DIR,
                    $providerId,
                    $period,
                    $direction,
                    $metric
                );
                self::atomicWrite($filename, (string)$value);
            }
        }
    }

    /**
     * Writes content to file atomically via temp file + rename.
     * Prevents Zabbix agent from reading a truncated/empty file.
     */
    private static function atomicWrite(string $filename, string $content): void
    {
        $tmp = $filename . '.tmp';
        if (file_put_contents($tmp, $content) !== false) {
            rename($tmp, $filename);
        }
    }

    /**
     * Removes cache files for providers that no longer exist.
     *
     * @param string[] $activeProviderIds
     */
    private static function cleanupOrphanedFiles(array $activeProviderIds): void
    {
        $files = glob(self::CACHE_DIR . '/trunkCalls_*');
        if (!is_array($files)) {
            return;
        }
        foreach ($files as $file) {
            $basename = basename($file);
            // Extract provider ID: trunkCalls_{providerId}_{period}_{direction}_{metric}
            $parts = explode('_', $basename, 3);
            $fileProviderId = $parts[1] ?? '';
            if ($fileProviderId !== '' && !in_array($fileProviderId, $activeProviderIds, true)) {
                unlink($file);
            }
        }
    }
}

// Run collector
StatsCollector::collect();
