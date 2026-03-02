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

namespace Modules\ModuleZabbixAgent5\Lib\RestAPI\Status\Actions;

use MikoPBX\Modules\PbxExtensionUtils;
use MikoPBX\PBXCoreREST\Lib\PBXApiResult;

/**
 * Streams the Zabbix template YAML for download via fpassthru.
 *
 * @package Modules\ModuleZabbixAgent5\Lib\RestAPI\Status\Actions
 */
class DownloadTemplateAction
{
    private const MODULE_UNIQUE_ID = 'ModuleZabbixAgent5';

    /**
     * @param array<string, mixed> $data Request data (unused)
     * @return PBXApiResult
     */
    public static function main(array $data): PBXApiResult
    {
        $result = new PBXApiResult();
        $result->processor = __METHOD__;

        $moduleDir = PbxExtensionUtils::getModuleDir(self::MODULE_UNIQUE_ID);
        $templatePath = $moduleDir . '/bin/zbx_export_templates.yaml';

        if (!file_exists($templatePath) || !is_readable($templatePath)) {
            $result->success = false;
            $result->messages['error'][] = 'Template file not found';
            $result->httpCode = 404;
            return $result;
        }

        $fileSize = filesize($templatePath);

        $result->success = true;
        $result->data = [
            'fpassthru' => [
                'filename' => $templatePath,
                'content_type' => 'application/x-yaml',
                'download_name' => 'zbx_mikopbx_template.yaml',
                'need_delete' => false,
                'additional_headers' => [],
            ],
        ];

        if ($fileSize !== false) {
            $result->data['fpassthru']['additional_headers']['Content-Length'] = (string)$fileSize;
        }

        return $result;
    }
}
