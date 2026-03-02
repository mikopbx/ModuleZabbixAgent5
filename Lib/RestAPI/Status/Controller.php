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

use MikoPBX\PBXCoreREST\Controllers\BaseRestController;
use MikoPBX\PBXCoreREST\Attributes\{
    ApiResource,
    ApiOperation,
    ApiResponse,
    ApiDataSchema,
    HttpMapping,
    SecurityType,
    ResourceSecurity
};

/**
 * Zabbix Agent Status Controller (Pattern 3 — auto-discovery with PHP 8 attributes)
 *
 * Provides service status and Zabbix template download endpoints.
 *
 * @package Modules\ModuleZabbixAgent5\Lib\RestAPI\Status
 */
#[ApiResource(
    path: '/pbxcore/api/v3/module-zabbix-agent5/status',
    tags: ['Module Zabbix Agent 5 - Status'],
    description: 'Zabbix Agent service status and template download',
    processor: Processor::class
)]
#[HttpMapping(
    mapping: [
        'GET' => ['getStatus', 'downloadTemplate']
    ],
    resourceLevelMethods: [],
    collectionLevelMethods: ['getStatus', 'downloadTemplate'],
    customMethods: ['getStatus', 'downloadTemplate']
)]
#[ResourceSecurity('module-zabbix-agent5-status', requirements: [SecurityType::LOCALHOST, SecurityType::BEARER_TOKEN])]
class Controller extends BaseRestController
{
    protected string $processorClass = Processor::class;

    /**
     * Get Zabbix Agent service status.
     *
     * @route GET /pbxcore/api/v3/module-zabbix-agent5/status:getStatus
     */
    #[ApiDataSchema(schemaClass: DataStructure::class, type: 'detail')]
    #[ApiOperation(
        summary: 'Get Zabbix Agent service status',
        description: 'Returns running state, PID, version, listen port, and server address',
        operationId: 'getZabbixAgentStatus'
    )]
    #[ApiResponse(200, 'Service status retrieved')]
    #[ApiResponse(500, 'rest_response_500')]
    public function getStatus(): void {}

    /**
     * Download Zabbix monitoring template YAML file.
     *
     * @route GET /pbxcore/api/v3/module-zabbix-agent5/status:downloadTemplate
     */
    #[ApiOperation(
        summary: 'Download Zabbix template',
        description: 'Streams the zbx_export_templates.yaml file for import into Zabbix server',
        operationId: 'downloadZabbixTemplate'
    )]
    #[ApiResponse(200, 'Template file streamed')]
    #[ApiResponse(404, 'Template file not found')]
    #[ApiResponse(500, 'rest_response_500')]
    public function downloadTemplate(): void {}
}
