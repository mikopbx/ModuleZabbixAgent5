<?php
/*
 * MikoPBX - free phone system for small business
 * Copyright © 2017-2024 Alexey Portnov and Nikolay Beketov
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

use MikoPBX\Common\Providers\PBXCoreRESTClientProvider;

require_once 'Globals.php';

class AsteriskInfo
{
    public const INNER_CALL = 0;
    public const IN_CALL = 1;
    public const OUT_CALL = 2;
    public const MAX_LEN_NUM = 6;

    /**
     * Calls REST API v3 endpoint and returns parsed data.
     * Handles output buffering (suppresses REST client error output)
     * and v3 response envelope unwrapping.
     */
    private static function callApi(string $path): ?array
    {
        try {
            ob_start();
            $di = \Phalcon\Di\Di::getDefault();
            $restAnswer = $di->get(PBXCoreRESTClientProvider::SERVICE_NAME, [
                $path,
                PBXCoreRESTClientProvider::HTTP_METHOD_GET
            ]);
            ob_end_clean();
            if (!$restAnswer->success) {
                return null;
            }
            $data = $restAnswer->data;
            if (is_string($data)) {
                $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            }
            // Handle v3 API envelope {result, data, ...}
            if (is_array($data) && array_key_exists('result', $data)) {
                return $data['result'] ? ($data['data'] ?? []) : null;
            }
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            return null;
        }
    }

    /**
     * Retrieves all provider statuses (SIP + IAX) from v3 API.
     * Returns flat array of provider objects.
     */
    private static function getAllProviderStatuses(): array
    {
        $data = self::callApi('/pbxcore/api/v3/sip-providers:getStatuses');
        if ($data === null) {
            return [];
        }
        $providers = [];
        foreach (['sip', 'iax'] as $type) {
            if (isset($data[$type]) && is_array($data[$type])) {
                foreach ($data[$type] as $provider) {
                    $providers[] = $provider;
                }
            }
        }
        return $providers;
    }

    // Counts the total number of active calls
    public static function countCalls(): void
    {
        echo count(self::getActiveCalls());
    }

    // Retrieves active calls from the PBX status API
    public static function getActiveCalls(): array
    {
        $data = self::callApi('/pbxcore/api/v3/pbx-status:getActiveCalls');
        return is_array($data) ? $data : [];
    }

    // Counts the number of incoming calls
    public static function countInCalls(): void
    {
        $ch = 0;
        foreach (self::getActiveCalls() as $row) {
            if (self::IN_CALL === self::getCallDirection($row)) {
                $ch++;
            }
        }
        echo $ch;
    }

    // Determines the direction of a call based on the length of source and destination numbers
    public static function getCallDirection(array $row): int
    {
        $result = self::OUT_CALL;
        if (strlen($row['src_num'] ?? '') < self::MAX_LEN_NUM
            && strlen($row['dst_num'] ?? '') < self::MAX_LEN_NUM) {
            $result = self::INNER_CALL;
        } elseif (strlen($row['src_num'] ?? '') >= self::MAX_LEN_NUM) {
            $result = self::IN_CALL;
        }
        return $result;
    }

    // Counts the number of outgoing calls
    public static function countOutCalls(): void
    {
        $ch = 0;
        foreach (self::getActiveCalls() as $row) {
            if (self::OUT_CALL === self::getCallDirection($row)) {
                $ch++;
            }
        }
        echo $ch;
    }

    // Counts the number of internal calls
    public static function countInnerCalls(): void
    {
        $ch = 0;
        foreach (self::getActiveCalls() as $row) {
            if (self::INNER_CALL === self::getCallDirection($row)) {
                $ch++;
            }
        }
        echo $ch;
    }

    // Counts total SIP extensions via v3 API
    public static function getCountSipPeers(): void
    {
        $data = self::callApi('/pbxcore/api/v3/sip:getStatuses');
        echo ($data !== null) ? count($data) : 0;
    }

    // Counts active (online) SIP peers
    public static function getCountActivePeers(): void
    {
        $ch = 0;
        $data = self::callApi('/pbxcore/api/v3/sip:getStatuses');
        if ($data !== null) {
            foreach ($data as $peer) {
                if (($peer['status'] ?? '') === 'OK') {
                    $ch++;
                }
            }
        }
        echo $ch;
    }

    // Counts active providers (state OK or REGISTERED)
    public static function getCountActiveProviders(): void
    {
        $ch = 0;
        foreach (self::getAllProviderStatuses() as $provider) {
            $state = $provider['state'] ?? '';
            if ($state === 'OK' || $state === 'REGISTERED') {
                $ch++;
            }
        }
        echo $ch;
    }

    // Counts non-active providers (failed or unregistered, excludes disabled)
    public static function getCountNonActiveProviders(): void
    {
        $ch = 0;
        foreach (self::getAllProviderStatuses() as $provider) {
            $state = $provider['state'] ?? '';
            if ($state !== 'OK' && $state !== 'REGISTERED' && $state !== 'OFF') {
                $ch++;
            }
        }
        echo $ch;
    }

    // Returns Zabbix LLD JSON with discovered providers (SIP + IAX)
    public static function discoveryTrunks(): void
    {
        $result = [];
        foreach (self::getAllProviderStatuses() as $provider) {
            $id = $provider['id'] ?? '';
            if ($id === '') {
                continue;
            }
            $result[] = [
                '{#TRUNKID}' => $id,
                '{#TRUNKNAME}' => $provider['description'] ?? ($provider['username'] ?? $id),
                '{#TRUNKHOST}' => $provider['host'] ?? '',
            ];
        }
        echo json_encode($result);
    }

    // Returns status of a specific provider by ID
    public static function trunkStatus(string $trunkId = ''): void
    {
        $result = 'UNKNOWN';
        foreach (self::getAllProviderStatuses() as $provider) {
            if (($provider['id'] ?? '') === $trunkId) {
                $result = $provider['state'] ?? 'UNKNOWN';
                break;
            }
        }
        echo $result;
    }

}

// Main logic to call the appropriate function based on the command line argument
$action = $argv[1] ?? '';
$args = array_slice($argv ?? [], 2);
if (method_exists(AsteriskInfo::class, $action)) {
    AsteriskInfo::$action(...$args);
}
