# CLAUDE.md

This file records only repository-specific contracts whose violation causes failures.

## Non-obvious contracts

- **Generated JavaScript:** Edit only `public/assets/js/src/*.js`. Rebuild each changed source with:

  ```bash
  /Users/nb/PhpstormProjects/mikopbx/MikoPBXUtils/node_modules/.bin/babel "$INPUT_FILE" \
    --out-dir "$OUTPUT_DIR" \
    --source-maps inline \
    --presets airbnb
  ```

  Set `OUTPUT_DIR` to `public/assets/js`. Do not hand-edit tracked files in that directory. If `babel-preset-airbnb` is unavailable, do not install dependencies or modify generated output without explicit approval.

- **PHP verification:** There is no module test suite. Run PHPStan for every changed PHP file with `--memory-limit=1G`; syntax checks alone are not sufficient.

- **Minimal target shell:** Scripts under `bin/` and `Setup/zabbix/scripts/` run under `/bin/sh` on a BusyBox-based system. Avoid Bash-only syntax and preserve explicit BusyBox applet use where it exists.

- **Standalone bootstrap:** CLI and worker entry points use `require_once 'Globals.php'` and cannot assume the web application's autoloader or DI bootstrap. Keep this bootstrap and the shared `ApiHelper` path intact.

- **Config-editor WAF exemption:** `ZabbixAgent5Conf::getWafExemptions()` must continue exempting `/admin-cabinet/module-zabbix-agent5/save` from `body-scan`. Valid Zabbix `UserParameter` content contains shell pipelines and SQL-like text that otherwise causes false-positive request rejection.

- **CDR statistics cache:** Zabbix polling must read trunk-call statistics from `/storage/usbdisk1/mikopbx/tmp/ModuleZabbixAgent5`. Collection remains a five-minute cron job and writes with a temporary file followed by an atomic rename. Do not replace cached reads with REST calls per Zabbix item; that creates a request thundering herd.

- **UserParameter arity:** The generated `asterisk[*]` line must pass `$1` through `$5` to `asterisk-stats.sh`. Change `fixUserParameter()` and the script dispatch together if argument semantics change.

- **Agent binaries:** `bin/zabbix_agentd` and `bin/zabbix_agentd_arm` are CI artifacts and stay gitignored. Do not add locally built binaries.

- **Template UUIDs:** Every `uuid:` in `bin/zbx_export_templates.yaml` is a dashless 32-hex UUIDv4: character 13 is `4`, and character 17 is `8`, `9`, `a`, or `b`. One invalid value makes Zabbix reject the entire import.
