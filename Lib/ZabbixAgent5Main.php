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
namespace Lib;

use MikoPBX\Core\System\Processes;
use MikoPBX\Core\System\System;
use MikoPBX\Core\System\Util;
use MikoPBX\Modules\PbxExtensionUtils;
use Modules\ModuleZabbixAgent5\Models\ModuleZabbixAgent5;
use Phalcon\Di\Injectable;

/**
 * Class ZabbixAgent5Main
 *
 * Main class for managing the Zabbix Agent 5 module.
 *
 * @package Lib
 */
class ZabbixAgent5Main extends Injectable
{
    const SERVICE_ZABBIX_AGENT = 'zabbix_agentd';

    /**
     * @var array Directories used by the module
     */
    public array $dirs;

    /**
     * @var string Unique identifier for the module
     */
    private string $moduleUniqueID = 'ModuleZabbixAgent5';

    /**
     * @var array Module settings
     */
    private array $module_settings = [];

    /**
     * ZabbixAgent5Main constructor.
     * Initializes module directories, checks module status and loads settings.
     */
    public function __construct()
    {
        // Get the module directories
        $this->dirs = $this->getModuleDirs();

        // Check if the module is enabled
        if (PbxExtensionUtils::isEnabled($this->moduleUniqueID)) {

            // Retrieve the module settings from the database
            $module_settings = ModuleZabbixAgent5::findFirst();
            if ($module_settings !== null) {
                $this->module_settings = $module_settings->toArray();
            }
        }

        if (empty($this->module_settings['configContent'])) {
            $this->module_settings['configContent'] = file_get_contents($this->dirs['binDir'] . '/zabbix_agentd_default.conf');
        }
    }

    /**
     * Prepares and returns the directories required by the module.
     *
     * @return array Associative array containing paths of directories used by the module.
     */
    private function getModuleDirs(): array
    {
        // moduleDir
        $moduleDir = PbxExtensionUtils::getModuleDir($this->moduleUniqueID);

        // binDir
        $binDir = $moduleDir . '/bin';
        Util::mwMkdir($binDir);

        // confDir
        $confDir = "/etc/custom_modules/{$this->moduleUniqueID}";
        Util::mwMkdir($confDir);

        // logDir
        $logDir = System::getLogDir();
        $logDir = "{$logDir}/{$this->moduleUniqueID}";
        Util::mwMkdir($logDir);

        // pid
        $pidDir = "/var/run/custom_modules/{$this->moduleUniqueID}";
        Util::mwMkdir($pidDir);

        return [
            'logDir' => $logDir,
            'confDir' => $confDir,
            'pidDir' => $pidDir,
            'binDir' => $binDir,
        ];
    }


    /**
     * Retrieves the listening port for the Zabbix agent from the configuration.
     *
     * @return int The port number on which the Zabbix agent is configured to listen.
     */
    public static function getListenPort(): int
    {
        // Extract ListenPort from the configuration
        $defaultPort = 10050;
        $regex = '/^ListenPort=(\d+)/m';
        $main = new ZabbixAgent5Main();
        if (preg_match($regex, $main->module_settings['configContent'], $matches)) {
            $listenPort = $matches[1];
        } else {
            $listenPort = $defaultPort;
        }
        return $listenPort;
    }

    /**
     * Stops the Zabbix agent service.
     */
    public static function stopService(): void
    {
        $main = new ZabbixAgent5Main();
        $service = self::SERVICE_ZABBIX_AGENT;
        $path = "{$main->dirs['binDir']}/{$service}";
        Processes::processWorker($path, '', $service, 'stop');
    }

    /**
     * Starts the Zabbix agent service.
     */
    public static function startService(): void
    {
        $main = new ZabbixAgent5Main();
        $service = self::SERVICE_ZABBIX_AGENT;
        $path = "{$main->dirs['binDir']}/{$service}";
        $configPath = "{$main->dirs['confDir']}/zabbix_agentd.conf";
        file_put_contents($main->module_settings['configContent'], $configPath);
        Processes::processWorker($path, '-c ' . $configPath, $service, 'restart');
    }

}