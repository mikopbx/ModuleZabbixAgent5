#!/bin/sh

ASTERISK='/usr/sbin/asterisk';
PHP_INFO="$(dirname "$0")/AsteriskInfo.php";

status(){
  pidData=$(/bin/busybox ps | /bin/busybox grep '/usr/sbin/asterisk -f' | /bin/busybox grep -v grep);
  if [ "${pidData}x" = "x" ]; then
    echo 0
  else
    echo 1
  fi
}

version(){
  v=$("$ASTERISK" -V | /bin/busybox cut -d ' ' -f 2 );
  echo "$v";
}

statusReload(){
    reloadTime=$("$ASTERISK" -rx "core show uptime seconds" | awk -F": " '/Last reload/{print$2}');
    if [ -z "$reloadTime" ];then
        echo "Asterisk has not been reloaded yet"
    else
        printf '%dd:%dh:%dm:%ds\n' $((reloadTime/86400)) $((reloadTime%86400/3600)) $((reloadTime%3600/60)) $((reloadTime%60))
    fi
}
statusUptime(){
    upTime=$("$ASTERISK" -rx "core show uptime seconds" | awk -F ": " '/System uptime/{print$2}');
    if [ -z "$upTime" ];then
        echo "Asterisk is not up"
    else
        printf '%dd:%dh:%dm:%ds\n' $((upTime/86400)) $((upTime%86400/3600)) $((upTime%3600/60)) $((upTime%60))
    fi
}

callsActive(){
    CALL=$("$ASTERISK" -rx "core show channels" | grep "active call"| awk '{print$1}');
    echo "$CALL"
}

channelsActive(){
    CHANNEL=$("$ASTERISK" -rx "core show channels" | grep "active channels" | awk '{print $1}')
    echo "$CHANNEL"
}

callsProcessed(){
    CALL=$("$ASTERISK" -rx "core show channels" |grep "calls processed"|awk '{print$1}');
    echo "$CALL"
}

sipTrunkDown(){
    php -f "$PHP_INFO" getCountNonActiveProviders;
}

countSipPeers(){
    php -f "$PHP_INFO" getCountSipPeers;
}

countInCalls(){
    php -f "$PHP_INFO" countInCalls;
}

countOutCalls(){
    php -f "$PHP_INFO" countOutCalls;
}

countInnerCalls(){
    php -f "$PHP_INFO" countOutCalls;
}

CountActiveProviders(){
    php -f "$PHP_INFO" getCountActiveProviders;
}

CountNonActiveProviders(){
    php -f "$PHP_INFO" getCountNonActiveProviders;
}

CountActivePeers(){
    php -f "$PHP_INFO" getCountActivePeers;
}

### Execute the argument
$1