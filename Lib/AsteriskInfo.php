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

// Define the namespace for this class
namespace Modules\ModuleZabbixAgent5\Lib;

// Include necessary classes and exceptions
use JsonException;
use Modules\ModuleZabbixAgent5\Lib\MikoPBXVersion;
use MikoPBX\Common\Models\Extensions;
use MikoPBX\Common\Providers\PBXCoreRESTClientProvider;


// Include global variables and functions
require_once 'Globals.php';

// Class to gather and process Asterisk information
class AsteriskInfo
{
    // Define constants for call directions and maximum length of numbers
    public const INNER_CALL = 0;
    public const IN_CALL = 1;
    public const OUT_CALL = 1;
    public const MAX_LEN_NUM = 6;

    // Counts the total number of active calls and outputs the count
    public static function countCalls(): void
    {
        echo count(self::getActiveCalls());
    }

    // Retrieves active calls from the CDR (Call Detail Record) database
    public static function getActiveCalls(): array
    {
        try {
            // Get user data from the API
            $di = MikoPBXVersion::getDefaultDi();
            $restAnswer = $di->get(PBXCoreRESTClientProvider::SERVICE_NAME, [
                '/pbxcore/api/cdr/getActiveCalls',
                PBXCoreRESTClientProvider::HTTP_METHOD_GET
            ]);
            if (!$restAnswer->success){
                return [];
            }
            $result = json_decode($restAnswer->data, true, 512, JSON_THROW_ON_ERROR)??[];
        } catch (JsonException $e) {
            // In case of JSON decoding error, return an empty array
            $result = [];
        }
        return $result;
    }

    // Counts the number of incoming calls and outputs the count
    public static function countInCalls(): void
    {
        $ch = 0;
        $calls = self::getActiveCalls();
        foreach ($calls as $row) {
            if (self::IN_CALL === self::getCallDirection($row)) {
                $ch++;
            }
        }
        echo $ch;
    }

    // Determines the direction of a call based on the length of source and destination numbers
    public static function getCallDirection(array $row): int
    {
        $result = self::OUT_CALL;
        if (strlen($row['src_num']) < self::MAX_LEN_NUM
            && strlen($row['dst_num']) < self::MAX_LEN_NUM) {
            $result = self::INNER_CALL;
        } elseif (strlen($row['src_num']) >= self::MAX_LEN_NUM) {
            $result = self::IN_CALL;
        }
        return $result;
    }

    // Counts the number of outgoing calls and outputs the count
    public static function countOutCalls(): void
    {
        $ch = 0;
        $calls = self::getActiveCalls();
        foreach ($calls as $row) {
            if (self::OUT_CALL === self::getCallDirection($row)) {
                $ch++;
            }
        }
        echo $ch;
    }

    // Counts the number of internal calls and outputs the count
    public static function countInnerCalls(): void
    {
        $ch = 0;
        $calls = self::getActiveCalls();
        foreach ($calls as $row) {
            if (self::INNER_CALL === self::getCallDirection($row)) {
                $ch++;
            }
        }
        echo $ch;
    }

    // Counts the number of SIP extensions and outputs the count
    public static function getCountSipPeers(): void
    {
        $extensions = Extensions::find("type='SIP'")->toArray();
        echo count($extensions);
    }

    // Counts the number of active SIP peers and outputs the count
    public static function getCountActivePeers(): void
    {
        $ch = 0;
        // Get user data from the API
        $di = MikoPBXVersion::getDefaultDi();
        $restAnswer = $di->get(PBXCoreRESTClientProvider::SERVICE_NAME, [
            '/pbxcore/api/sip/getPeersStatuses',
            PBXCoreRESTClientProvider::HTTP_METHOD_GET
        ]);
        if (!$restAnswer->success){
            return;
        }
        $peers = $restAnswer->data;
        foreach ($peers as $peer) {
            if ("OK" === $peer['state'] && is_numeric($peer['id'])) {
                $ch++;
            }
        }
        echo $ch;
    }

    // Counts the number of active providers (registrations) and outputs the count
    public static function getCountActiveProviders(): void
    {
        $ch = 0;

        // Get user data from the API
        $di = MikoPBXVersion::getDefaultDi();
        $restAnswer = $di->get(PBXCoreRESTClientProvider::SERVICE_NAME, [
            '/pbxcore/api/sip/getRegistry',
            PBXCoreRESTClientProvider::HTTP_METHOD_GET
        ]);
        if (!$restAnswer->success){
            return;
        }

        $peers = $restAnswer->data;
        foreach ($peers as $peer) {
            if ("OK" === $peer['state'] || 'REGISTERED' === $peer['state']) {
                $ch++;
            }
        }
        echo $ch;
    }

    // Counts the number of non-active providers (failed or unregistered) and outputs the count
    public static function getCountNonActiveProviders(): void
    {
        $ch = 0;
        // Get user data from the API
        $di = MikoPBXVersion::getDefaultDi();
        $restAnswer = $di->get(PBXCoreRESTClientProvider::SERVICE_NAME, [
            '/pbxcore/api/sip/getRegistry',
            PBXCoreRESTClientProvider::HTTP_METHOD_GET
        ]);
        if (!$restAnswer->success){
            return;
        }
        $peers = $restAnswer->data;
        foreach ($peers as $peer) {
            if ("OK" !== $peer['state'] && 'REGISTERED' !== $peer['state'] && 'OFF' !== $peer['state']) {
                $ch++;
            }
        }
        echo $ch;
    }

}

// Main logic to call the appropriate function based on the command line argument
$action = $argv[1] ?? '';
if (method_exists(AsteriskInfo::class, $action)) {
    AsteriskInfo::$action();
}