<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 12 2019
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
