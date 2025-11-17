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


---

## 🎯 [1.4.0] — 2025-11-15

### ✨ Added (Phase 3: UX Improvements)
- **Actionable Suggestions** — Every issue now includes `suggestion` field with fix recommendations
  - Suggestions appear in CLI output after the lint table with `[Suggestion #N]` headers
  - Suggestions included in JSON output as `suggestion` field for tool integration
  - Each suggestion provides clear, actionable next steps
- **Documentation Links** — Issues now include optional `docsUrl` field
  - Links appear in CLI with 📖 icon and full URL
  - JSON output includes `docs_url` field for programmatic access
  - All built-in rules updated with relevant documentation links
- **Enhanced AbstractRule.warn()** — Signature extended to accept `$suggestion` and `$docsUrl` parameters
  - Fully backward compatible (optional parameters)
  - Enables custom rule authors to provide rich feedback

### 🧰 Improved
- **Reporter System**: Enhanced `renderTable()` and `renderJson()` to display/include suggestions
- **Built-in Rules Updated**: AddNonNullableColumnWithoutDefault and MissingIndexOnForeignKey now include actionable suggestions
- **Documentation**: Updated usage.md with "Understanding Suggestions" section
- **Developer Experience**: Custom rule authors can now provide suggestions via `warn()` method

### 📊 Example Output
```bash
[Suggestion #1] AddNonNullableColumnWithoutDefault:
  Option 1: Add a default value to the column (e.g., ->default('value'))
  Option 2: Make it nullable with ->nullable(), backfill existing rows, then alter
  📖 Learn more: https://docs.example.com/rules#AddNonNullableColumnWithoutDefault
```

### 🧪 Testing
- Baseline generation test fixed (filename alignment: migration-linter-baseline.json)
- Changes fully backward compatible with v1.3.x

---

🧠 Tip: You can always check your installed version via Composer:
```bash
composer show sufyandev/laravel-migration-linter
```
Or compare changes on GitHub:  
👉 <a href="https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package" target="_blank">muhammad-sufyan5/sufyan-laravel-migration-lint-package</a>

