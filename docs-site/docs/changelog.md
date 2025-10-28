---
id: changelog
title: 🗓️ Changelog
sidebar_position: 8
---

All notable changes to **Laravel Migration Linter** are documented here.  
This project follows [Semantic Versioning](https://semver.org/).

---

## 🧩 [1.1.0] — 2025-10-21

### 🆕 Added
- **`DropColumnWithoutBackup`** rule — warns when columns are dropped without confirmation or backup.  
- **`AddUniqueConstraintOnNonEmptyColumn`** rule — warns when adding unique constraints that might fail on existing data.  
- **`FloatColumnForMoney`** rule — warns when using `float()` for monetary fields; recommends `decimal(10,2)` instead.

### ⚙️ Improved
- Enhanced output formatting for compact mode (`--compact`) on smaller terminals.  
- Configuration system now supports **custom rules** from any namespace (e.g., `App\MigrationRules`).  

### 🧰 Developer
- Added rule discovery improvements in `RuleEngine`.  
- Documentation updates (`rules.md`, `writing-custom-rules.md`, and `configuration.md`).

---

## 🚀 [1.0.0] — 2025-10-15

### 🆕 Added
- **Core Artisan command:**
  ```bash
  php artisan migrate:lint
  ```
Base rules:
- AddNonNullableColumnWithoutDefault
  - MissingIndexOnForeignKey
  - Config publishing (php artisan vendor:publish --tag="migration-linter-config")
- Baseline file support (--generate-baseline, --baseline=path)
- JSON output mode (--json)
- Compact report output for smaller terminals.
---

🧠 Tip: You can always check your installed version via Composer:
```bash
composer show sufyandev/laravel-migration-linter
```
Or compare changes on GitHub:  
👉 <a href="https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package" target="_blank">muhammad-sufyan5/sufyan-laravel-migration-lint-package</a>

