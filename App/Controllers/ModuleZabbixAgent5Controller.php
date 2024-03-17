<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 11 2018
 */
namespace Modules\ModuleZabbixAgent5\App\Controllers;
use Lib\ZabbixAgent5Main;
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
