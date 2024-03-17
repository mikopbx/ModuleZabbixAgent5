<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 2 2019
 */

namespace Modules\ModuleZabbixAgent5\Models;

use MikoPBX\Modules\Models\ModulesModelsBase;

class ModuleZabbixAgent5 extends ModulesModelsBase
{

    /**
     * @Primary
     * @Identity
     * @Column(type="integer", nullable=false)
     */
    public $id;

    /**
     * Zabbix agent config file
     *
     * @Column(type="string", nullable=true)
     */
    public $configContent;


    public function initialize(): void
    {
        $this->setSource('m_ModuleZabbixAgent5');
        parent::initialize();
    }

}
