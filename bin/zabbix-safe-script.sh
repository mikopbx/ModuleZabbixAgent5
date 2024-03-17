#!/bin/sh

# Get the directory where this script is located
scriptDir=$(/bin/busybox dirname "$0")

# Path to the configuration file
confFile="/etc/custom_modules/ModuleZabbixAgent5/zabbix_agentd.conf"

# Function to start zabbix_agentd
start_agent() {
    # Assuming the zabbix_agentd executable is in the same directory as the script
    "$scriptDir/zabbix_agentd" -c "$confFile"
}

# Check if a Zabbix agent process with this configuration file is already running
pidData=$(/bin/busybox ps | /bin/busybox grep "$confFile" | /bin/busybox grep -v grep)

# Check for the 'restart' parameter
if [ "$1" = "restart" ]; then
    # If 'restart' parameter is provided, kill existing zabbix_agentd processes
    if [ -n "$pidData" ]; then
        echo "Restarting zabbix_agentd..."
        pid=$(echo "$pidData" | /bin/busybox awk '{print $1}')
        /bin/busybox kill "$pid"
        # Wait a bit to ensure the process has been terminated
        sleep 1
    else
        echo "zabbix_agentd is not running. Starting it..."
    fi
    # Start zabbix_agentd
    start_agent
elif [ -z "$pidData" ]; then
    # If no process is found and 'restart' parameter is not provided, just start zabbix_agentd
    echo "Starting zabbix_agentd..."
    start_agent
else
    echo "zabbix_agentd is already running."
fi
