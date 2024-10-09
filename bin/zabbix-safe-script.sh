#!/bin/sh

# Get the directory where this script is located
scriptDir=$(/bin/busybox dirname "$0")

# Path to the configuration file
confFile="/etc/custom_modules/ModuleZabbixAgent5/zabbix_agentd.conf"

# Determine architecture
arch=$(uname -m)

# Set binary name based on architecture
if [[ "$arch" == "x86_64" || "$arch" == "i386" || "$arch" == "i686" ]]; then
    agentBinary="zabbix_agentd"
elif [[ "$arch" == "armv7l" || "$arch" == "armv8" || "$arch" == "aarch64" ]]; then
    agentBinary="zabbix_agentd_arm"
else
    echo "Unsupported architecture: $arch"
    exit 1
fi

# Function to start the appropriate zabbix_agentd binary
start_agent() {
    # Assuming the zabbix_agentd executable is in the same directory as the script
    "$scriptDir/$agentBinary" -c "$confFile"
}

# Check if a Zabbix agent process with this configuration file is already running
pidData=$(/bin/busybox ps | /bin/busybox grep "$confFile" | /bin/busybox grep -v grep)

# Check for the 'restart' parameter
if [ "$1" = "restart" ]; then
    # If 'restart' parameter is provided, kill existing zabbix_agentd processes
    if [ -n "$pidData" ]; then
        echo "Restarting $agentBinary..."
        pid=$(echo "$pidData" | /bin/busybox awk '{print $1}')
        /bin/busybox kill "$pid"
        # Wait a bit to ensure the process has been terminated
        sleep 1
    else
        echo "$agentBinary is not running. Starting it..."
    fi
    # Start the agent
    start_agent
elif [ -z "$pidData" ]; then
    # If no process is found and 'restart' parameter is not provided, just start zabbix_agentd
    echo "Starting $agentBinary..."
    start_agent
else
    echo "$agentBinary is already running."
fi