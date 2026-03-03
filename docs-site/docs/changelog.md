---
id: changelog
title: "Changelog"
sidebar_position: 8
---

All notable changes to **Laravel Migration Linter** are documented here.  
This project follows [Semantic Versioning](https://semver.org/).

---

## 🚀 [v2.1.1] — 2026-03-03

### 🐛 Fixed
- **migrate:lint no longer tries to write HTML report by default**
  - Fixed --html option parsing so plain php artisan migrate:lint does not attempt report generation.
  - Prevents invalid ? path writes on Windows environments (Git Bash / Laragon / XAMPP users).

- **Safer HTML path resolution when --html is provided**
  - --html now generates to default path storage/app/migration-lint-report.html when no custom path is supplied.
  - Custom --html=your/path/report.html behavior remains unchanged.

### 🧪 Developer
- Added regression tests to prevent future --html option handling breakage.
- Removed hardcoded package `version` from composer.json so Packagist versioning is fully tag-driven.

---

## 🚀 [v2.1.0] — 2025-12-24

### 🆕 Added
- **New Rule: `RenamingColumnWithoutIndex`** — Detects column rename operations that can cause table locks and downtime
  - Warns when using `$table->renameColumn()` on large tables
  - Provides 3-phase zero-downtime migration strategy
  - Configurable to check large tables only or all tables
  - Supports safe comment bypass: `// safe rename`

- **New Rule: `ChangeColumnTypeOnLargeTable`** — Detects column type changes that can cause table locks
  - Detects 25+ column type methods with `->change()` modifier (string, integer, decimal, text, datetime, boolean, enum, etc.)
  - Default severity: error (high impact operation)
  - Provides 3 migration strategies: zero-downtime, maintenance window, pt-online-schema-change
  - Supports safe comment bypass: `// safe change`, `// maintenance window`

- **New Flag: `--no-suggestions`** — Hide migration suggestions for cleaner output
  - Useful when you only want to see the warnings table
  - Can be combined with `--summary` for minimal output

- **New Flag: `--html=`** — Generate interactive HTML reports
  - Beautiful, responsive HTML reports with charts and visualizations
  - Searchable and filterable issue table
  - Grouped suggestions organized by rule type
  - Rule breakdown with statistics
  - Perfect for sharing with team members and CI/CD artifacts
  - Example: `php artisan migrate:lint --html=storage/report.html`

### ⚙️ Improved
- **Enhanced MigrationParser** — Now properly skips commented-out lines
  - Lines starting with `//` or `/*` are ignored during parsing
  - Prevents false positives from commented code
  - Tracks previous line context for safe comment detection
  
- **Better Safe Comment Detection** — Comments on line above operations are now recognized
  - `// safe rename` on line before operation works correctly
  - `/* safe rename */` before operation works correctly
  - Inline comments continue to work: `$table->renameColumn(...); // safe rename`

- **Improved Suggestion Output Formatting** — Cleaner, more organized display
  - Suggestions now grouped by rule type instead of repeated for each occurrence
  - Added visual hierarchy with section headers and separators
  - Shows occurrence count per rule (e.g., "3 occurrences")
  - Better indentation and color coding for readability
  - Professional CLI output with proper spacing

### 🧰 Developer
- Added 13 comprehensive unit tests for `RenamingColumnWithoutIndex` rule
- Added 16 comprehensive unit tests for `ChangeColumnTypeOnLargeTable` rule
- Added 12 comprehensive unit tests for `HtmlReporter` class
- Total: 84 tests passing (200 assertions)
- Parser improvements benefit all existing rules
- Enhanced rawCode context includes previous line for better analysis
- New `HtmlReporter` class for generating interactive reports

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

## 🛠️ [2.0.0] — 2025-11-20

### ✨ Added (SOLID Principles Refactoring)
- **8 Core Interfaces** — Dependency injection contracts
  - `ConfigInterface`, `FormatterInterface`, `ParserInterface`, `RuleInterface`, `RuleEngineInterface`
  - `SeverityResolverInterface`, `ReporterInterface`, `BaselineInterface`
- **3 Service Classes** — Reusable business logic
  - `LaravelConfigProvider` — Bridges Laravel config to contracts
  - `SeverityResolver` — Priority-based severity determination
  - `LintService` — Orchestrates entire linting workflow
- **5 Formatter Classes** — Modular output system
  - `TableFormatter` — Console table format (with Symfony Table component)
  - `JsonFormatter` — JSON output for CI/CD
  - `CompactFormatter` — Single-line compact format
  - `SummaryFormatter` — Table + statistics
  - `BaseFormatter` — Shared utilities for all formatters

### 🔧 Improved
- **SOLID Principles** throughout:
  - Single Responsibility — Each formatter, service, rule has one job
  - Open/Closed — Add new formatters/services without modifying existing code
  - Liskov Substitution — All formatters interchangeable via interface
  - Interface Segregation — Small, focused contracts
  - Dependency Inversion — Depend on interfaces, not implementations
- **Table Formatting** — Fixed color code alignment
  - Switched to Symfony's native `Table` component
  - Perfect column alignment regardless of content
  - Proper text wrapping and spacing
- **Dependency Injection** — Service provider auto-wiring
  - Laravel container bindings for all services
  - Automatic resolver injection into rules
  - Testable with mocked interfaces

### ✅ Quality
- **144 tests passing** (259 assertions)
- **100% backward compatible** (zero breaking changes)
- **~95% code coverage** (excellent test quality)

### 🔄 Migration
All commands work identically — no breaking changes:
```bash
php artisan migrate:lint              # Still works
php artisan migrate:lint --json       # Still works
php artisan migrate:lint --compact    # Still works
php artisan migrate:lint --summary    # Still works
php artisan migrate:lint --rules      # Still works
```

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


