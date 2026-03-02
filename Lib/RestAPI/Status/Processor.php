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

use MikoPBX\PBXCoreREST\Lib\PBXApiResult;
use Modules\ModuleZabbixAgent5\Lib\RestAPI\Status\Actions\GetStatusAction;
use Modules\ModuleZabbixAgent5\Lib\RestAPI\Status\Actions\DownloadTemplateAction;
use Phalcon\Di\Injectable;

/**
 * Processor — routes requests to Action classes for Status resource.
 *
 * @package Modules\ModuleZabbixAgent5\Lib\RestAPI\Status
 */
class Processor extends Injectable
{
    /**
     * Processes the Status request.
     *
     * @param array<string, mixed> $request The request data containing 'action' and other parameters
     * @return PBXApiResult
     */
    public static function callBack(array $request): PBXApiResult
    {
        $res = new PBXApiResult();
        $res->processor = __METHOD__;
        $action = $request['action'] ?? '';

        switch ($action) {
            case 'getStatus':
                $res = GetStatusAction::main($request['data'] ?? []);
                break;
            case 'downloadTemplate':
                $res = DownloadTemplateAction::main($request['data'] ?? []);
                break;
            default:
                $res->messages['error'][] = "Unknown action - $action in " . __CLASS__;
                break;
        }

        $res->function = $action;
        return $res;
    }
}
