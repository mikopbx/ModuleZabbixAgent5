#!/bin/sh

# Paths to the Asterisk executable and the PHP script
ASTERISK='/usr/sbin/asterisk';
PHP_INFO="$(dirname "$0")/../Lib/AsteriskInfo.php";

# Function to check if Asterisk is running
status(){
  # Use busybox to check for Asterisk's process
  pidData=$(/bin/busybox ps | /bin/busybox grep '/usr/sbin/asterisk' | /bin/busybox grep -v grep);
  # If no process is found, return 0, otherwise return 1
  if [ "${pidData}x" = "x" ]; then
    echo 0
  else
    echo 1
  fi
}

# Function to get the version of Asterisk
version(){
  # Extract and print the version number from the Asterisk version string
  v=$("$ASTERISK" -V | /bin/busybox cut -d ' ' -f 2 );
  echo "$v";
}

# Function to check the last reload time of Asterisk
statusReload(){
    # Get the last reload time in seconds and convert it to human-readable format
    reloadTime=$("$ASTERISK" -rx "core show uptime seconds" | awk -F": " '/Last reload/{print$2}');
    if [ -z "$reloadTime" ];then
        echo "Asterisk has not been reloaded yet"
    else
        printf '%dd:%dh:%dm:%ds\n' $((reloadTime/86400)) $((reloadTime%86400/3600)) $((reloadTime%3600/60)) $((reloadTime%60))
    fi
}

# Function to check the uptime of Asterisk
statusUptime(){
    # Get the system uptime in seconds and convert it to human-readable format
    upTime=$("$ASTERISK" -rx "core show uptime seconds" | awk -F ": " '/System uptime/{print$2}');
    if [ -z "$upTime" ];then
        echo "Asterisk is not up"
    else
        printf '%dd:%dh:%dm:%ds\n' $((upTime/86400)) $((upTime%86400/3600)) $((upTime%3600/60)) $((upTime%60))
    fi
}

# Function to get the number of active calls
callsActive(){
    # Extract and print the number of active calls
    CALL=$("$ASTERISK" -rx "core show channels" | grep "active call"| awk '{print$1}');
    echo "$CALL"
}

# Function to get the number of active channels
channelsActive(){
    # Extract and print the number of active channels
    CHANNEL=$("$ASTERISK" -rx "core show channels" | grep "active channels" | awk '{print $1}')
    echo "$CHANNEL"
}

# Function to get the number of processed calls
callsProcessed(){
    # Extract and print the number of processed calls
    CALL=$("$ASTERISK" -rx "core show channels" |grep "calls processed"|awk '{print$1}');
    echo "$CALL"
}

# Function to check the number of SIP trunks that are down
sipTrunkDown(){
    # Call the PHP script to get the count of non-active SIP providers
    php -f "$PHP_INFO" getCountNonActiveProviders;
}

# Function to count SIP peers
countSipPeers(){
    # Call the PHP script to count SIP peers
    php -f "$PHP_INFO" getCountSipPeers;
}

# Function to count incoming calls
countInCalls(){
    # Call the PHP script to count incoming calls
    php -f "$PHP_INFO" countInCalls;
}

# Function to count outgoing calls
countOutCalls(){
    # Call the PHP script to count outgoing calls
    php -f "$PHP_INFO" countOutCalls;
}

# Function to count internal calls
countInnerCalls(){
    # Call the PHP script to count internal calls
    php -f "$PHP_INFO" countInnerCalls;
}

# Function to count active SIP providers
CountActiveProviders(){
    # Call the PHP script to count active SIP providers
    php -f "$PHP_INFO" getCountActiveProviders;
}

# Function to count non-active SIP providers
CountNonActiveProviders(){
    # Call the PHP script to count non-active SIP providers
    php -f "$PHP_INFO" getCountNonActiveProviders;
}

# Function to count active SIP peers
CountActivePeers(){
    # Call the PHP script to count active SIP peers
    php -f "$PHP_INFO" getCountActivePeers;
}

# Function to discover SIP trunks (Zabbix LLD)
discoveryTrunks(){
    php -f "$PHP_INFO" discoveryTrunks;
}

# Function to get registration status of a specific trunk
trunkStatus(){
    php -f "$PHP_INFO" trunkStatus "$1";
}

# Execute the function passed as an argument with whitelist validation
case "$1" in
    status|version|statusReload|statusUptime|callsActive|channelsActive|callsProcessed|\
sipTrunkDown|countSipPeers|countInCalls|countOutCalls|countInnerCalls|\
CountActiveProviders|CountNonActiveProviders|CountActivePeers|discoveryTrunks)
        "$1"
        ;;
    trunkStatus)
        trunkStatus "$2"
        ;;
    *)
        echo "ZBX_NOTSUPPORTED"
        exit 1
        ;;
esac
