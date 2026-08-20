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

# Function to get CDR call statistics for a specific trunk from cache
# Args: trunkId period direction metric
# Cache is populated by zabbix-stats-collector.sh every 5 minutes
trunkCalls(){
    # Validate parameters to prevent path injection
    case "$2" in hour|day) ;; *) echo 0; return;; esac
    case "$3" in incoming|outgoing|all) ;; *) echo 0; return;; esac
    case "$4" in totalCalls|answeredCalls|totalDuration|totalBillsec) ;; *) echo 0; return;; esac

    CACHE_DIR="/storage/usbdisk1/mikopbx/tmp/ModuleZabbixAgent5"
    CACHE_FILE="${CACHE_DIR}/trunkCalls_${1}_${2}_${3}_${4}"
    if [ -f "$CACHE_FILE" ]; then
        /bin/busybox cat "$CACHE_FILE"
    else
        echo 0
    fi
}

# Function to list the top 10 processes by resident memory
# Output: one line per process, "RSS_MB NAME PID", sorted by RSS descending.
# Kernel threads have no VmRSS line and are skipped automatically.
topMem(){
    # Errors are suppressed: processes may die between glob expansion and read
    /bin/busybox cat /proc/[0-9]*/status 2>/dev/null \
    | /bin/busybox awk '
        /^Name:/  { name=$2; for (i=3; i<=NF; i++) { name = name "_" $i } next }
        /^Pid:/   { pid=$2; next }
        /^VmRSS:/ { printf "%d %s %s\n", $2, pid, name }
      ' \
    | /bin/busybox sort -rn \
    | /bin/busybox head -n 10 \
    | /bin/busybox awk '{ printf "%.1fM %s %s\n", $1/1024, $3, $2 }'
}

# Function to list the top 10 processes by CPU usage
# Two samples of /proc/<pid>/stat one second apart; the percentage is the delta
# of (utime+stime) divided by the delta of the total jiffies of all CPUs.
# 100% therefore means the full capacity of all cores ("Irix off" in top terms).
# Output: one line per process, "CPU% NAME PID", sorted descending.
topCpu(){
    {
        /bin/busybox cat /proc/stat /proc/[0-9]*/stat 2>/dev/null
        echo SEP
        /bin/busybox sleep 1
        /bin/busybox cat /proc/stat /proc/[0-9]*/stat 2>/dev/null
    } | /bin/busybox awk '
        $1 == "SEP" { phase=2; next }
        $1 == "cpu" {
            t=0
            for (i=2; i<=NF; i++) { t+=$i }
            if (phase == 2) { total2=t } else { total1=t }
            next
        }
        $1 ~ /^[0-9]+$/ {
            # comm may contain spaces and parentheses: take everything between
            # the first "(" and the last ")", numeric fields start after it
            if (match($0, /\(.*\)/) == 0) { next }
            pid=$1
            name=substr($0, RSTART+1, RLENGTH-2)
            gsub(/[ \t]/, "_", name)
            # after ")" the first field is state (field 3), so utime (field 14)
            # is f[12] and stime (field 15) is f[13]
            if (split(substr($0, RSTART+RLENGTH+1), f) < 13) { next }
            if (phase == 2) { t2[pid]=f[12]+f[13]; nm[pid]=name } else { t1[pid]=f[12]+f[13] }
        }
        END {
            dt = total2 - total1
            if (dt <= 0) { exit }
            for (p in t2) {
                if (p in t1) {
                    d = t2[p] - t1[p]
                    if (d > 0) { printf "%.1f %s %s\n", d*100/dt, nm[p], p }
                }
            }
        }
      ' \
    | /bin/busybox sort -rn \
    | /bin/busybox head -n 10 \
    | /bin/busybox awk '{ printf "%s%% %s %s\n", $1, $2, $3 }'
}

# Execute the function passed as an argument with whitelist validation
case "$1" in
    status|version|statusReload|statusUptime|callsActive|channelsActive|callsProcessed|\
sipTrunkDown|countSipPeers|countInCalls|countOutCalls|countInnerCalls|\
CountActiveProviders|CountNonActiveProviders|CountActivePeers|discoveryTrunks|\
topMem|topCpu)
        "$1"
        ;;
    trunkStatus)
        trunkStatus "$2"
        ;;
    trunkCalls)
        trunkCalls "$2" "$3" "$4" "$5"
        ;;
    *)
        echo "ZBX_NOTSUPPORTED"
        exit 1
        ;;
esac
