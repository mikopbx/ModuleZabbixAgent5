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

declare(strict_types=1);

namespace Modules\ModuleZabbixAgent5\Lib\RestAPI\Status\Actions;

use MikoPBX\Core\System\Processes;
use MikoPBX\PBXCoreREST\Lib\PBXApiResult;
use Modules\ModuleZabbixAgent5\Lib\ZabbixAgent5Main;

/**
 * Returns Zabbix Agent service status.
 *
 * @package Modules\ModuleZabbixAgent5\Lib\RestAPI\Status\Actions
 */
class GetStatusAction
{
    /**
     * @param array<string, mixed> $data Request data (unused)
     * @return PBXApiResult
     */
    public static function main(array $data): PBXApiResult
    {
        $result = new PBXApiResult();
        $result->processor = __METHOD__;

        $pid = Processes::getPidOfProcess(ZabbixAgent5Main::SERVICE_ZABBIX_AGENT);
        $running = !empty($pid);

        $version = '';
        if ($running) {
            $main = new ZabbixAgent5Main();
            $binPath = $main->dirs['binDir'] . '/zabbix_agentd';
            if (file_exists($binPath)) {
                $output = [];
                exec($binPath . ' -V 2>&1', $output);
                $versionLine = $output[0] ?? '';
                if (preg_match('/(\d+\.\d+\.\d+)/', $versionLine, $m)) {
                    $version = $m[1];
                }
            }
        }

        $main = $main ?? new ZabbixAgent5Main();
        $config = $main->module_settings['configContent'] ?? '';

        $listenPort = ZabbixAgent5Main::getListenPort();

        $server = '';
        if (preg_match('/^Server=(.*)$/m', $config, $m)) {
            $server = trim($m[1]);
        }

        $result->success = true;
        $result->data = [
            'running' => $running,
            'pid' => $running ? (int)$pid : null,
            'version' => $version,
            'listenPort' => $listenPort,
            'server' => $server,
        ];

        return $result;
    }
}
