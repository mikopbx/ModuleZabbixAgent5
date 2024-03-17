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
namespace Modules\ModuleZabbixAgent5\App\Controllers;
use Modules\ModuleZabbixAgent5\Lib\ZabbixAgent5Main;
use MikoPBX\AdminCabinet\Controllers\BaseController;
use MikoPBX\AdminCabinet\Providers\AssetProvider;
use MikoPBX\Modules\PbxExtensionUtils;
use Modules\ModuleZabbixAgent5\App\Forms\ModuleZabbixAgent5Form;
use Modules\ModuleZabbixAgent5\Models\ModuleZabbixAgent5;

class ModuleZabbixAgent5Controller extends BaseController
{
    private $moduleUniqueID = 'ModuleZabbixAgent5';
    private $moduleDir;

    /**
     * Basic initial class
     */
    public function initialize(): void
    {
        $this->moduleDir           = PbxExtensionUtils::getModuleDir($this->moduleUniqueID);
        $this->view->logoImagePath = "{$this->url->get()}assets/img/cache/{$this->moduleUniqueID}/logo.svg";
        $this->view->submitMode    = null;
        parent::initialize();
    }

    /**
     * Index page controller
     */
    public function indexAction(): void
    {
        $footerCollection = $this->assets->collection(AssetProvider::FOOTER_JS);
        $footerCollection->addJs('js/pbx/main/form.js', true);
        $footerCollection->addJs("js/cache/{$this->moduleUniqueID}/module-zabbix-agent5-index.js", true);

        $footerCollectionACE = $this->assets->collection(AssetProvider::FOOTER_ACE);
        $footerCollectionACE
            ->addJs('js/vendor/ace/ace.js', true)
            ->addJs('js/vendor/ace/mode-julia.js', true);

        $headerCollectionCSS = $this->assets->collection('headerCSS');
        $headerCollectionCSS->addCss("css/cache/{$this->moduleUniqueID}/module-zabbix-agent5.css", true);

        $settings = ModuleZabbixAgent5::findFirst();
        if ($settings === null) {
            $settings = new ModuleZabbixAgent5();
            $main = new ZabbixAgent5Main();
            $settings->configContent=$main->module_settings['configContent'];
        }
        $this->view->form = new ModuleZabbixAgent5Form($settings);
        $this->view->pick("{$this->moduleDir}/App/Views/index");
    }

    /**
     * Save settings AJAX action
     */
    public function saveAction() :void
    {
        if ( ! $this->request->isPost()) {
            return;
        }
        $data   = $this->request->getPost();
        $record = ModuleZabbixAgent5::findFirst();

        if ($record === null) {
            $record = new ModuleZabbixAgent5();
        }
        foreach ($record as $key => $value) {
            switch ($key) {
                case 'id':
                    break;
                default:
                    if (array_key_exists($key, $data)) {
                        $record->$key = $data[$key];
                    } else {
                        $record->$key = '';
                    }
            }
        }

       $this->saveEntity($record);
    }

}
