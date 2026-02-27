## ModuleZabbixAgent5

This module provides integration with Zabbix Agent 5 for monitoring your MikoPBX system. It allows you to configure and control the Zabbix agent, enabling you to collect and send various metrics about your PBX to a Zabbix server for monitoring and analysis.

### Inputs

The main input for this module is the Zabbix agent configuration file, which can be modified through the web interface. This file dictates how the agent operates, including which metrics to collect, how often to collect them, and where to send the data.  The module includes a default configuration file that can be used as a starting point. It is recommended to familiarize yourself with the Zabbix agent configuration options to customize the monitoring to your specific needs.  The module also provides several pre-defined user parameters that can be used to collect specific metrics from Asterisk, such as the number of active calls, processed calls, active channels, and status information about SIP peers and trunks. These parameters can be used directly in your Zabbix templates.

### Outputs

The module outputs various metrics about your MikoPBX system to the configured Zabbix server. These metrics can include: 

* Number of active calls
* Number of processed calls
* Number of active channels
* Status of SIP peers (active/inactive)
* Status of SIP trunks (active/inactive)
* Asterisk uptime
* Asterisk last reload time
* Asterisk version

These metrics can be used to create graphs, alerts, and dashboards in your Zabbix server to monitor the health and performance of your MikoPBX system.  You can leverage the pre-defined Zabbix templates provided to quickly set up basic monitoring for your PBX.  Additionally, you can use the low-level discovery features of Zabbix to automatically discover and monitor new SIP peers and trunks as they are added to your system.
=======
