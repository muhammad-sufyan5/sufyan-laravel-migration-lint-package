---
id: changelog
title: 🗓️ Changelog
sidebar_position: 8
---

All notable changes to **Laravel Migration Linter** are documented here.  
This project follows [Semantic Versioning](https://semver.org/).

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

### New Rules
Initial public release with baseline rule set:
- AddNonNullableColumnWithoutDefault
- MissingIndexOnForeignKey
- DropColumnWithoutBackup
- AddUniqueConstraintOnNonEmptyColumn
- FloatColumnForMoney

---

## [v1.2.0] — 2025-10-30

### ✨ Added
- **AddNonNullableColumnWithoutDefault**
  - Detects `.change()` on existing columns.
  - Skips `Schema::create()` (safe for new tables).
- **MissingIndexOnForeignKey**
  - Detects `foreignId()` without `->constrained()`.
  - Detects `morphs()` / `nullableMorphs()` without `->index()`.
  - Detects composite `foreign([...])` without matching `index([...])`.
- **DropColumnWithoutBackup**
  - Detects multiple column drops.
  - Supports safe comment whitelist (`// safe drop` or `/* safe-drop */`).
- **AddUniqueConstraintOnNonEmptyColumn**
  - Detects composite unique constraints.
  - Detects inline `->unique()` and `->unique()->change()`.
  - Configurable `check_composite` flag.
- **FloatColumnForMoney**
  - Detects `float()`, `double()`, and `real()` for money-like columns.
  - Smarter pattern matching (price, amount, total, tax, etc.).
  - Configurable toggles: `check_double` and `check_real`.

### 🧰 Improved
- Unified severity handling via config.
- More informative lint messages for each rule.
- Added full Pest test coverage for all five rules.
- Enhanced documentation and configuration examples.

### 🐛 Fixed
- Config overrides now correctly respect `enabled = false`.
- RuleEngine dynamically skips disabled rules during lint runs.
- Reporter tests aligned with real Laravel OutputStyle.

---


🧠 Tip: You can always check your installed version via Composer:
```bash
composer show sufyandev/laravel-migration-linter
```
Or compare changes on GitHub:  
👉 <a href="https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package" target="_blank">muhammad-sufyan5/sufyan-laravel-migration-lint-package</a>

