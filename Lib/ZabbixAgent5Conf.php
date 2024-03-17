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

use Lib\ZabbixAgent5Main;
use MikoPBX\Modules\Config\ConfigClass;
use Modules\ModuleZabbixAgent5\Models\ModuleZabbixAgent5;

class ZabbixAgent5Conf extends ConfigClass
{
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
     * @param $data
     */
    public function modelsEventChangeData($data): void
    {
        if ($data['model'] === ModuleZabbixAgent5::class) {
            ZabbixAgent5Main::startService();
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

}
