# Laravel Migration Linter — Development Workflow  
**Vendor:** sufyan  
**Author:** Sufyan  
**Current Progress:** Completed up to Step 5  

---

## 🏁 Step 1 — Package Setup

### 🎯 Goal  
Create the base package structure, initialize Composer, and prepare autoloading for Laravel integration.

### 🧱 Actions Performed  
- Created package folder `sufyan-laravel-migration-lint-package` under the `task-manager` directory.  
- Initialized Git (`git init`) and Composer (`composer init`) as a **library** type package.  
- Required dependencies:  
  - `illuminate/support`, `spatie/laravel-package-tools`  
  - Dev tools: `orchestra/testbench`, `pestphp/pest`, `laravel/pint`, `nunomaduro/larastan`.  
- Configured PSR-4 autoloading to map `Sufyan\\MigrationLinter\\` → `src/`.  
- Added Laravel auto-discovery for the `MigrationLinterServiceProvider`.  
- Created folder structure:  
  `src/`, `src/Commands/`, `src/Rules/`, `src/Support/`, `config/`, `tests/`.  
- Confirmed autoload generation and Git committed.

### ✅ Verification  
`composer dump-autoload` ran successfully.  
Dependencies installed with no errors.  

---

## ⚙️ Step 2 — Service Provider & Config

### 🎯 Goal  
Register the package with Laravel and define configurable options.

### 🧱 Actions Performed  
- Added `MigrationLinterServiceProvider` extending `Spatie\LaravelPackageTools\PackageServiceProvider`.  
- Registered:  
  - Package name: `migration-linter`  
  - Config file via `->hasConfigFile()`  
  - Command reference via `->hasCommand(LintMigrations::class)` (implemented later).  
- Created `config/migration-linter.php` defining settings for:  
  - `enabled`, `environments`, `severity_threshold`  
  - `large_table_names`, `exclude_paths`, and per-rule toggles.  
- Published config successfully using  
  `php artisan vendor:publish --tag="migration-linter-config"`.  

### ✅ Verification  
`php artisan package:discover` recognized the package.  
Config file published correctly to the Laravel app’s `/config` directory.

---

## ⚙️ Step 3 — Artisan Command (`migrate:lint`)

### 🎯 Goal  
Add a working command to trigger the migration linter.

### 🧱 Actions Performed  
- Created `src/Commands/LintMigrations.php`.  
- Implemented command signature `migrate:lint` with options:  
  `--path`, `--json`, `--baseline`.  
- Added logic to:
  - Detect and validate target migration path.
  - List all migration files and simulate scanning.
- Verified registration with Laravel’s Artisan CLI.  

### ✅ Verification  
`php artisan list | findstr migrate:lint` shows command registered.  
Running `php artisan migrate:lint` lists all migration files and confirms successful scan.

---

## 🧠 Step 4 — Migration Parser

### 🎯 Goal  
Parse migration files to extract schema operations (`Schema::create` / `Schema::table`).

### 🧱 Actions Performed  
- Created `src/Support/MigrationParser.php`.  
- Implemented logic to:
  - Iterate over all files in the provided path.  
  - Detect `Schema::create` / `Schema::table` closures.  
  - Capture `$table->...()` operations (method name + arguments).  
  - Return parsed data as arrays of operations.  
- Tested using Laravel Tinker to confirm detection of table names, methods, and arguments.

### ✅ Verification  
Tinker successfully returned structured data for migration operations.  
Parser now feeds data to the rule engine.

---

## ⚙️ Step 5 — Rule Engine & First Rule

### 🎯 Goal  
Implement a rules framework for analyzing parsed operations and reporting potential issues.

### 🧱 Actions Performed  
- Added **core classes** in `/src`:  
  - `Rules/AbstractRule.php` → base class for all rules.  
  - `Support/Operation.php` → represents a parsed schema operation.  
  - `Support/Issue.php` → represents a detected linting issue.  
  - `Support/RuleEngine.php` → loads and runs enabled rules from config.  
- Created **first rule**:  
  - `AddNonNullableColumnWithoutDefault` detects addition of NOT NULL columns without defaults.  
- Integrated rule engine into `migrate:lint` command to output results.  
- Fixed type mismatch (array → `Operation` object conversion).  
- Added **hybrid configuration**:  
  - New config key `check_all_tables` (default `false`).  
  - When `true`, rule checks all tables.  
  - When `false`, checks only configured `large_table_names`.  
- Successfully verified that running `php artisan migrate:lint` outputs warnings for unsafe columns.

### ✅ Verification  
Command lists potential issues for unsafe column additions.  
Hybrid logic confirmed working via toggling `check_all_tables`.  

### 📘 Notes  
- Rule 1 complete (`AddNonNullableColumnWithoutDefault`).  
- Config defaults use selective (large-table) mode.  
- Next step: implement the **Reporter** for pretty CLI output and JSON mode.

---

## 🧾 Step 6 — Reporter & JSON Output

### 🎯 Goal  
Enhance the CLI and CI/CD experience by producing clean, colorized lint reports in table format and supporting structured JSON output.

---

### 🧱 Actions Performed  
- Created a new helper class at `src/Support/Reporter.php`.  
- The Reporter handles:
  - Displaying human-readable CLI output with colored severity levels (`info`, `warning`, `error`).
  - Printing results in JSON format when the user runs `php artisan migrate:lint --json`.
  - Returning exit codes for CI/CD pipelines based on the configured severity threshold.
- Updated the `migrate:lint` command to use the Reporter instead of manual printing.
- Added color styling (red = error, yellow = warning, cyan = info) for better visibility.
- Implemented `exitCode()` logic to respect `severity_threshold` from the configuration file:
  - Exits `0` if no issues or only below-threshold severities.
  - Exits `1` if any issue meets or exceeds the configured threshold.

---

### ⚙️ Configuration Impact  
- No new config keys introduced in this step.  
- Uses the existing `severity_threshold` value from `config/migration-linter.php`.

---

### 🧪 Verification  
Run the following commands inside the Laravel app using this package:

```bash
php artisan migrate:lint
php artisan migrate:lint --json > storage/lint-report.json
```

## 🧩 Step 7 — Column Name Extraction & Enhancements

### 🎯 Goal
Enhance migration parsing and linting logic to detect exact column names, improve message clarity, and ensure proper propagation across all layers (parser → operation → rule → reporter).

- Enhanced the Migration Parser to detect column names by extracting the first argument of `$table->method('column')`.
- Added a new `column` key to every parsed operation.
- Each operation now includes:
  - `table`, `method`, `args`, and `column` attributes.
- Parser now recognizes column names for regular schema calls (e.g., `string('name')`, `integer('age')`).

**Verification**
```bash
php artisan tinker
```
$parser = new \Sufyan\MigrationLinter\Support\MigrationParser();
collect($parser->parse(base_path('database/migrations')))->take(3);

## 🧩 Rule: MissingIndexOnForeignKey

### Goal
Introduce a new linting rule that detects foreign key-like columns (ending with `_id`) that are added without an index or foreign constraint.

### Actions Performed
- Added a new rule class: `MissingIndexOnForeignKey`.
- The rule checks for:
  - Column creation methods (`unsignedBigInteger`, `integer`, etc.).
  - Column names ending with `_id`.
  - Absence of `->index()` or `->foreign()` statements.
- Added this rule to the `RuleEngine` map and enabled it in the configuration.
- Now warns developers when they add a foreign key column without indexing.

### Verification
Run:
```bash
php artisan migrate:lint
```

## 🧩 Step 8 — Add Severity Levels for Rules

### Goal
Allow each linting rule to define its own severity level (`info`, `warning`, or `error`) and support overriding via configuration.

### Actions Performed
- Added a `severity()` method to the `AbstractRule` base class.
- Updated the `warn()` helper to respect rule-specific severity.
- Modified all existing rules (`AddNonNullableColumnWithoutDefault`, `MissingIndexOnForeignKey`) to define default severity.
- Enhanced the configuration to allow per-rule overrides:
  php
  'rules' => [
      'AddNonNullableColumnWithoutDefault' => [
          'enabled' => true,
          'severity' => 'error',
      ],
      'MissingIndexOnForeignKey' => [
          'enabled' => true,
          'severity' => 'warning',
      ],
  ],

