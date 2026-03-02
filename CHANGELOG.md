# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Service status indicator in the web UI
- Zabbix template download directly from the module interface
- Disk usage monitoring for /storage partition
- Per-trunk CDR call statistics via cdr:getStatsByProvider API
- SIP trunk low-level discovery (LLD), with associated triggers and graphs in the Zabbix template

### Changed
- Switched to REST API v3 auto-discovery pattern with fpassthru for template download
- Switched to PHP 8.4; now requires MikoPBX 2025.1.1+
- API URLs now use absolute paths without globalRootUrl prefix

### Fixed
- Zabbix template UUIDs corrected to valid UUIDv4 format
- Template download now uses fetch with Bearer token and fpassthru
- Provider state comparison updated for API v3 lowercase responses
- Agent restart race condition and Asterisk status detection
- Zabbix template structure: moved triggers and graphs to top level

## [1.27] - 2026-02-27

### Fixed
- Critical bugs and hardened error handling

## [1.26] - 2026-02-27

### Changed
- Zabbix agent binary is now built statically in CI; prebuilt binaries removed from the repository

### Fixed
- MikoPBXVersion namespace corrected from Lib to Modules\ModuleZabbixAgent5\Lib
- Remount call for offload (#837)

## [1.25] - 2024-11-19

### Added
- Croatian translation via Weblate

## [1.24] - 2024-11-11

### Changed
- Updated README with latest module documentation

## [1.23] - 2024-11-11

### Added
- GitHub Actions workflow replacing TeamCity build pipeline
- GitHub workflow release_settings in module.json

## [1.19] - 2024-11-08

### Added
- ARM (aarch64) Zabbix agent binary

### Changed
- Migrated CI from TeamCity to GitHub Actions
- Updated Zabbix Agent to version 6.4.0
- Updated for PHP 8.3 and Phalcon 5.8 compatibility

## [1.18] - 2024-01-01

### Added
- Translations via Weblate for 29 languages
- Zabbix monitoring template included with the module

### Changed
- Removed direct RestApi class usage; renamed REST API libraries according to module template
- Module refactoring for improved code structure

### Fixed
- Data type corrections
- Default configuration values

## [1.0] - 2023-01-01

- Initial release

[Unreleased]: https://github.com/mikopbx/ModuleZabbixAgent5/compare/v1.27...develop
[1.27]: https://github.com/mikopbx/ModuleZabbixAgent5/compare/v1.26...v1.27
[1.26]: https://github.com/mikopbx/ModuleZabbixAgent5/compare/v1.25...v1.26
[1.25]: https://github.com/mikopbx/ModuleZabbixAgent5/compare/v1.24...v1.25
[1.24]: https://github.com/mikopbx/ModuleZabbixAgent5/compare/v1.23...v1.24
[1.23]: https://github.com/mikopbx/ModuleZabbixAgent5/compare/v1.19...v1.23
[1.19]: https://github.com/mikopbx/ModuleZabbixAgent5/compare/v1.18...v1.19
[1.18]: https://github.com/mikopbx/ModuleZabbixAgent5/compare/v1.0...v1.18
[1.0]: https://github.com/mikopbx/ModuleZabbixAgent5/releases/tag/v1.0
