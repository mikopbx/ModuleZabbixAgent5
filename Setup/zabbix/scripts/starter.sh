#!/bin/sh

confFile="$(/bin/busybox dirname "$(/bin/busybox dirname "$0")")/zabbix_agentd.conf";
pidData=$(/bin/busybox ps | /bin/busybox grep "$confFile" | /bin/busybox grep -v grep);
if [ -z "$pidData" ];then
  /usr/sbin/zabbix_agentd -c "$confFile";
fi