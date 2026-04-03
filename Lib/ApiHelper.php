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

use MikoPBX\Common\Providers\PBXCoreRESTClientProvider;

/**
 * Shared REST API helper for CLI scripts (AsteriskInfo, StatsCollector).
 * Both scripts bootstrap via require_once 'Globals.php' and cannot use autoloading,
 * so this file is included explicitly via require_once.
 */
class ApiHelper
{
    /**
     * Calls REST API v3 endpoint and returns parsed data.
     * Handles output buffering (suppresses REST client error output)
     * and v3 response envelope unwrapping.
     */
    public static function callApi(string $path, array $params = []): ?array
    {
        try {
            ob_start();
            $di = \Phalcon\Di\Di::getDefault();
            $restAnswer = $di->get(PBXCoreRESTClientProvider::SERVICE_NAME, [
                $path,
                PBXCoreRESTClientProvider::HTTP_METHOD_GET,
                $params
            ]);
            ob_end_clean();
            if (!$restAnswer->success) {
                return null;
            }
            $data = $restAnswer->data;
            if (is_string($data)) {
                $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            }
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
     * Returns all provider statuses (SIP + IAX) as a flat array.
     */
    public static function getAllProviderStatuses(): array
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
}
