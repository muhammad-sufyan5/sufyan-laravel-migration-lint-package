# 🧾 Changelog

All notable changes to this project will be documented in this file.  
This project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] – 2025-10-16
### Added
- Initial release of **Laravel Migration Linter**  
- Command: `php artisan migrate:lint`  
- Rules: AddNonNullableColumnWithoutDefault and MissingIndexOnForeignKey  
- Baseline support and severity thresholds  
- CI/CD workflow for automatic testing and Packagist integration

## [1.1.0] – 2025-10-21
### Added
- **DropColumnWithoutBackup** rule — warns when columns are dropped without confirmation.
- **AddUniqueConstraintOnNonEmptyColumn** rule — warns when adding unique constraints that might fail on existing data.
- **FloatColumnForMoney** rule — warns when using float() for monetary fields; recommends decimal(10,2).
### Improved
- Output formatting improvements in Reporter (truncated columns for smaller terminals).
### Notes
This release focuses on expanding migration safety coverage and improving usability for smaller terminal sizes.

