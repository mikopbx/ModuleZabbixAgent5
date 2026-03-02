# Contributing to ModuleZabbixAgent5

Thank you for your interest in contributing!

## Development Environment

- PHP 8.4 with Phalcon 5.x framework
- MikoPBX 2025.1.1+ for testing
- Node.js for JavaScript compilation

## Coding Standards

### PHP

- PSR-4 autoloading: `Modules\ModuleZabbixAgent5\` maps to repository root
- Follow PSR-12 coding style
- Use PHP 8.4 features where appropriate
- Run `phpstan` to check code quality after changes

### JavaScript

- Source files in `public/assets/js/src/`
- Compiled output in `public/assets/js/`
- Compile with Babel using airbnb preset:
  ```bash
  npx babel public/assets/js/src/ --out-dir public/assets/js/ --source-maps inline --presets airbnb
  ```

### Shell Scripts

- Scripts in `bin/` must use busybox-compatible syntax
- Use `/bin/sh` shebang (not `/bin/bash`)
- Use `/bin/busybox` commands where possible
- MikoPBX runs on a minimal Linux distribution without bash

## Translations

The module supports 29 languages. Translation files are in `Messages/` directory.

- All translation keys use `modzbx_` prefix
- When adding new strings, add them to **all** locale files in `Messages/`
- Translations are managed via Weblate -- consider contributing translations there

## Submitting Changes

1. Fork the repository
2. Create a feature branch from `develop`
3. Make your changes
4. Ensure `phpstan` passes for PHP changes
5. Compile JavaScript if you modified source files
6. Add translations to all locale files if you added new strings
7. Submit a pull request to the `develop` branch

## Reporting Issues

- Use GitHub Issues for bug reports and feature requests
- Include MikoPBX version, module version, and architecture (amd64/arm64)

## License

By contributing, you agree that your contributions will be licensed under GPL-3.0-or-later.
