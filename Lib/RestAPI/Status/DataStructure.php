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

namespace Modules\ModuleZabbixAgent5\Lib\RestAPI\Status;

use MikoPBX\PBXCoreREST\Lib\Common\AbstractDataStructure;
use MikoPBX\PBXCoreREST\Lib\Common\OpenApiSchemaProvider;

/**
 * Data structure for Zabbix Agent Status resource.
 *
 * @package Modules\ModuleZabbixAgent5\Lib\RestAPI\Status
 */
class DataStructure extends AbstractDataStructure implements OpenApiSchemaProvider
{
    public static function getListItemSchema(): array
    {
        return self::getDetailSchema();
    }

    public static function getDetailSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'running' => [
                    'type' => 'boolean',
                    'description' => 'Whether the Zabbix agent process is running',
                    'example' => true,
                ],
                'pid' => [
                    'type' => 'integer',
                    'nullable' => true,
                    'description' => 'Process ID of the running agent',
                    'example' => 12345,
                ],
                'version' => [
                    'type' => 'string',
                    'description' => 'Zabbix agent version',
                    'example' => '6.0.44',
                ],
                'listenPort' => [
                    'type' => 'integer',
                    'description' => 'Agent listen port',
                    'example' => 10050,
                ],
                'server' => [
                    'type' => 'string',
                    'description' => 'Zabbix server address from config',
                    'example' => '192.168.1.100',
                ],
            ],
        ];
    }

    public static function getRelatedSchemas(): array
    {
        return [];
    }

    public static function getParameterDefinitions(): array
    {
        return [
            'request' => [],
            'response' => [
                'running' => [
                    'type' => 'boolean',
                    'description' => 'Whether the Zabbix agent process is running',
                    'readOnly' => true,
                ],
                'pid' => [
                    'type' => 'integer',
                    'nullable' => true,
                    'description' => 'Process ID of the running agent',
                    'readOnly' => true,
                ],
                'version' => [
                    'type' => 'string',
                    'description' => 'Zabbix agent version',
                    'readOnly' => true,
                ],
                'listenPort' => [
                    'type' => 'integer',
                    'description' => 'Agent listen port',
                    'readOnly' => true,
                ],
                'server' => [
                    'type' => 'string',
                    'description' => 'Zabbix server address from config',
                    'readOnly' => true,
                ],
            ],
            'related' => [],
        ];
    }
}
