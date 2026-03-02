# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 1.27.x  | Yes                |
| < 1.27  | No                 |

## Reporting a Vulnerability

If you discover a security vulnerability in ModuleZabbixAgent5, please report it responsibly. **Do not open a public issue.**

Send an email to **help@miko.ru** with the following details:

- A clear description of the vulnerability
- Steps to reproduce the issue
- An assessment of the potential impact

We will acknowledge your report within **5 business days** and work with you to coordinate a disclosure timeline before any public announcement.

## Security Considerations

- **Zabbix agent network exposure.** The Zabbix agent listens on TCP port 10050. Restrict access by setting the `Server=` directive in `zabbix_agentd.conf` to the IP address of your Zabbix server only. Do not leave it open to untrusted networks.
- **Minimal runtime privileges.** The agent process runs with minimal privileges on MikoPBX, limiting the impact of any potential compromise.
- **UserParameter scripts.** The shell scripts invoked by UserParameter keys execute read-only commands to collect Asterisk metrics. They do not accept or process any external user input.
- **Template download authentication.** The Zabbix template download endpoint requires a valid MikoPBX session. Unauthenticated requests are rejected.
