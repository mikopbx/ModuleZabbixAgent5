<?php

namespace MikoPBX\Zabbikx\Workers;
use MikoPBX\Common\Models\Extensions;
use MikoPBX\PBXCoreREST\Lib\CdrDBProcessor;
use JsonException;
use MikoPBX\PBXCoreREST\Lib\SIPStackProcessor;

require_once 'Globals.php';

class AsteriskInfo{

    public const INNER_CALL = 0;
    public const IN_CALL    = 1;
    public const OUT_CALL   = 1;
    public const MAX_LEN_NUM= 6;


    public static function getCallDirection(array $row):int{

        $result = self::OUT_CALL;
        if(strlen($row['src_num']) < self::MAX_LEN_NUM
            && strlen($row['dst_num']) < self::MAX_LEN_NUM){
            $result = self::INNER_CALL;
        }elseif (strlen($row['src_num']) >= self::MAX_LEN_NUM){
            $result = self::IN_CALL;
        }
        return $result;
    }

    public static function getActiveCalls():array{
        $data = CdrDBProcessor::getActiveCalls()->data[0]??'';
        try {
            $result = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $result =  [];
        }
        return $result;
    }

    public static function countCalls():void{
        echo count(self::getActiveCalls());
    }

    public static function countInCalls():void{
        $ch = 0;
        $calls = self::getActiveCalls();
        foreach ($calls as $row){
            if(self::IN_CALL === self::getCallDirection($row)){
                $ch ++;
            }
        }
        echo $ch;
    }

    public static function countOutCalls():void{
        $ch = 0;
        $calls = self::getActiveCalls();
        foreach ($calls as $row){
            if(self::OUT_CALL === self::getCallDirection($row)){
                $ch ++;
            }
        }
        echo $ch;
    }

    public static function countInnerCalls():void{
        $ch = 0;
        $calls = self::getActiveCalls();
        foreach ($calls as $row){
            if(self::INNER_CALL === self::getCallDirection($row)){
                $ch ++;
            }
        }
        echo $ch;
    }

    public static function getCountSipPeers():void{
        $extensions = Extensions::find("type='SIP'")->toArray();
        echo count($extensions);
    }

    public static function getCountActivePeers():void{
        $ch = 0;
        $peers = SIPStackProcessor::getPeersStatuses()->data;
        foreach ($peers as $peer){
            if("OK" === $peer['state'] && is_numeric($peer['id'])){
                $ch++;
            }
        }
        echo $ch;
    }

    public static function getCountActiveProviders():void{
        $ch = 0;
        $peers = SIPStackProcessor::getRegistry()->data;
        foreach ($peers as $peer){
            if("OK" === $peer['state'] || 'REGISTERED' === $peer['state']){
                $ch++;
            }
        }
        echo $ch;
    }

    public static function getCountNonActiveProviders():void{
        $ch = 0;
        $peers = SIPStackProcessor::getRegistry()->data;
        foreach ($peers as $peer){
            if("OK" !== $peer['state'] && 'REGISTERED' !== $peer['state'] && 'OFF' !== $peer['state']){
                $ch++;
            }
        }
        echo $ch;
    }

}

$action = $argv[1]??'';
if(method_exists(AsteriskInfo::class, $action)){
    AsteriskInfo::$action();
}