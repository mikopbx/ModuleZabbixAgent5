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

use MikoPBX\Core\System\Processes;
use MikoPBX\Modules\Config\ConfigClass;
use MikoPBX\PBXCoreREST\Lib\PBXApiResult;
use Modules\ModuleZabbixAgent5\Models\ModuleZabbixAgent5;

class ZabbixAgent5Conf extends ConfigClass
{
    public function onAfterPbxStarted(): void
    {
        ZabbixAgent5Main::startService();
    }
    /**
     * Process after disable action in web interface
     *
     * @return void
     */
    public function onAfterModuleDisable(): void
    {
        ZabbixAgent5Main::stopService();
    }

    /**
     * Process after enables action in web interface
     *
     * @return void
     * @throws \Exception
     */
    public function onAfterModuleEnable(): void
    {
        ZabbixAgent5Main::startService();
    }

    /**
     * Обработчик события изменения данных в базе настроек mikopbx.db.
     *
     * @param mixed $data
     */
    public function modelsEventChangeData($data): void
    {
        if ($data['model'] === ModuleZabbixAgent5::class) {
            ZabbixAgent5Main::restartService();
        }
    }


    /**
     * Returns array of additional firewall rules for module
     *
     * @return array
     */
    public function getDefaultFirewallRules(): array
    {
        $zabbixListenPort = ZabbixAgent5Main::getListenPort();
        return [
            'ModuleZabbixAgent5' => [
                'rules'     => [
                    ['portfrom' => $zabbixListenPort,       'portto' => $zabbixListenPort,        'protocol' => 'tcp', 'name' => 'ZabbixListenPort'],
                ],
                'action'    => 'allow',
                'shortName' => 'Zabbix',
            ],
        ];
    }

    /**
     * Adds crond tasks
     *
     * @param $tasks
     */
    public function createCronTasks(&$tasks): void
    {
        $workerPath = $this->moduleDir.'/bin/zabbix-safe-script.sh';
        $tasks[]    = "*/5 * * * * {$workerPath} > /dev/null 2> /dev/null\n";
    }

    /**
     * Process module REST API requests.
     *
     * @param array $request GET/POST parameters with 'action' and 'data' keys
     * @return PBXApiResult
     */
    public function moduleRestAPICallback(array $request): PBXApiResult
    {
        $action = $request['action'] ?? '';

        return match ($action) {
            'status' => $this->getServiceStatus(),
            'download-template' => $this->downloadTemplate(),
            default => $this->createErrorResult("Unknown action: {$action}"),
        };
    }

    /**
     * Returns Zabbix Agent service status information.
     */
    private function getServiceStatus(): PBXApiResult
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

    /**
     * Returns Zabbix template YAML for download.
     */
    private function downloadTemplate(): PBXApiResult
    {
        $result = new PBXApiResult();
        $result->processor = __METHOD__;

        $templatePath = $this->moduleDir . '/bin/zbx_export_templates.yaml';
        if (!file_exists($templatePath)) {
            $result->success = false;
            $result->messages['error'][] = 'Template file not found';
            return $result;
        }

        $result->success = true;
        $result->data = [
            'filename' => 'zbx_mikopbx_template.yaml',
            'content' => file_get_contents($templatePath),
        ];

        return $result;
    }

    /**
     * Creates an error PBXApiResult.
     */
    private function createErrorResult(string $message): PBXApiResult
    {
        $result = new PBXApiResult();
        $result->processor = __METHOD__;
        $result->success = false;
        $result->messages['error'][] = $message;
        return $result;
    }
}
