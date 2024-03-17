<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 12 2019
 */


namespace Modules\ModuleZabbixAgent5\Lib;

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
        // Todo:: stop zabbix agent
    }

    /**
     * Process after enable action in web interface
     *
     * @return void
     * @throws \Exception
     */
    public function onAfterModuleEnable(): void
    {
        // Todo:: start zabbix agent
    }

    /**
     * Обработчик события изменения данных в базе настроек mikopbx.db.
     *
     * @param $data
     */
    public function modelsEventChangeData($data): void
    {
        if ($data['model'] === ModuleZabbixAgent5::class) {
            // Todo:: restart zabbix agent
        }
    }
}
