---
id: changelog
title: 🗓️ Changelog
sidebar_position: 8
---

All notable changes to **Laravel Migration Linter** are documented here.  
This project follows [Semantic Versioning](https://semver.org/).

---

## 🚀 [v2.1.0] — 2025-12-24

### 🆕 Added
- **New Rule: `RenamingColumnWithoutIndex`** — Detects column rename operations that can cause table locks and downtime
  - Warns when using `$table->renameColumn()` on large tables
  - Provides 3-phase zero-downtime migration strategy
  - Configurable to check large tables only or all tables
  - Supports safe comment bypass: `// safe rename`

### ⚙️ Improved
- **Enhanced MigrationParser** — Now properly skips commented-out lines
  - Lines starting with `//` or `/*` are ignored during parsing
  - Prevents false positives from commented code
  - Tracks previous line context for safe comment detection
- **Better Safe Comment Detection** — Comments on line above operations are now recognized
  - `// safe rename` on line before operation works correctly
  - `/* safe rename */` before operation works correctly
  - Inline comments continue to work: `$table->renameColumn(...); // safe rename`

### 🧰 Developer
- Added 13 comprehensive unit tests for `RenamingColumnWithoutIndex` rule
- Parser improvements benefit all existing rules
- Enhanced rawCode context includes previous line for better analysis

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
- Enhanced documentation and configuration examples.

### 🐛 Fixed
- Config overrides now correctly respect `enabled = false`.
- RuleEngine dynamically skips disabled rules during lint runs.

---


---

## 🎯 [1.4.0] — 2025-11-15

### ✨ Added (Phase 3: UX Improvements + New Rule)
- **Actionable Suggestions** — Every issue now includes `suggestion` field with fix recommendations
  - Suggestions appear in CLI output after the lint table with `[Suggestion #N]` headers
  - Suggestions included in JSON output as `suggestion` field for tool integration
  - Each suggestion provides clear, actionable next steps
- **Documentation Links** — Issues now include optional `docsUrl` field
  - Links appear in CLI with 📖 icon and full URL
  - JSON output includes `docs_url` field for programmatic access
  - All built-in rules updated with relevant documentation links
- **New Rule: SoftDeletesOnProduction** — Warns about soft deletes on large tables
  - Detects `softDeletes()` on tables in `large_table_names` config
  - Provides 3 alternatives: archive, hard delete, or add index on deleted_at
  - Includes suggestions and documentation links
- **Enhanced AbstractRule.warn()** — Signature extended to accept `$suggestion` and `$docsUrl` parameters
  - Fully backward compatible (optional parameters)
  - Enables custom rule authors to provide rich feedback

### 🧰 Improved
- **Reporter System**: Enhanced `renderTable()` and `renderJson()` to display/include suggestions
- **Built-in Rules Updated**: AddNonNullableColumnWithoutDefault and MissingIndexOnForeignKey now include actionable suggestions
- **Documentation**: Updated usage.md, rules.md, writing-custom-rules.md, ci-cd.md with new features and rules
- **Developer Experience**: Custom rule authors can now provide suggestions via `warn()` method

### 📊 Example Output
```bash
[warning] SoftDeletesOnProduction  
→ Using soft deletes on the 'users' table may impact query performance over time.

[Suggestion #1] SoftDeletesOnProduction:
  Option 1: Archive old data to a separate table
  Option 2: Use hard deletes if retention isn't required
  Option 3: Add an index on 'deleted_at' to improve query performance
  📖 Learn more: https://docs.example.com/rules#-softdeletesonproduction
```

### ✨ Overview
- Changes fully backward compatible with v1.3.x
- Total rule count: 6 rules (5 original + 1 new)

---

🧠 Tip: You can always check your installed version via Composer:
```bash
composer show sufyandev/laravel-migration-linter
```
Or compare changes on GitHub:  
👉 <a href="https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package" target="_blank">muhammad-sufyan5/sufyan-laravel-migration-lint-package</a>

