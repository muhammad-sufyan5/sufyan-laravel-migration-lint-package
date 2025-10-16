# 🧩 Laravel Migration Linter  
[![Migration Linter](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/actions/workflows/migration-linter.yml/badge.svg)](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/actions)

A lightweight Laravel package that **analyzes your database migrations** and warns you about risky schema changes — before they reach production.  

---

## 🚀 Features
✅ Detects dangerous migration operations (like adding non-nullable columns without defaults).  
✅ Warns about missing indexes on foreign key columns.  
✅ Configurable rule severities (info, warning, error).  
✅ Baseline support to ignore known legacy issues.  
✅ CLI report with JSON output & colorized table.  
✅ Ready for CI/CD integration (GitHub Actions support).  

---

## 📦 Installation
Install via Composer:  
```bash
composer require sufyan/laravel-migration-linter --dev
```
The package will auto-register via Laravel’s package discovery.

---

⚙️ Publishing Configuration
You can publish the configuration file to customize rule settings:

```bash
php artisan vendor:publish --tag="migration-linter-config"
```
This creates:
```bash
config/migration-linter.php
```

⚙️ Configuration

Default config file (config/migration-linter.php):
```bash
return [
    'severity_threshold' => 'warning',

    'rules' => [
        'AddNonNullableColumnWithoutDefault' => [
            'enabled'  => true,
            'severity' => 'warning',
        ],
        'MissingIndexOnForeignKey' => [
            'enabled'  => true,
            'severity' => 'warning',
        ],
    ],
];
```

🧩 Usage
Run the built-in Artisan command to lint all migration files:

```bash
php artisan migrate:lint
```
Generate Baseline (ignore known issues)
```bash
php artisan migrate:lint --generate-baseline
```
Example Output

```sql
🔍 Running Laravel Migration Linter...

⚠️  Lint Report
+-----------------------------------------------+-----------------------------+-----------+----------+--------------------------------------------------------------+
| File                                          | Rule                        | Column    | Severity | Message                                                      |
+-----------------------------------------------+-----------------------------+-----------+----------+--------------------------------------------------------------+
| 2025_10_15_000000_create_users_table.php      | AddNonNullableColumnWithoutDefault | name   | warning  | Adding NOT NULL column 'name' without default.               |
| 2025_10_18_000000_add_user_id_to_orders.php   | MissingIndexOnForeignKey    | user_id   | warning  | Foreign key-like column 'user_id' missing index or constraint.|
+-----------------------------------------------+-----------------------------+-----------+----------+--------------------------------------------------------------+

Linting complete. Found 2 issue(s).
```
🧰 GitHub Actions Integration
Add this workflow file: .github/workflows/migration-linter.yml
```bash
name: Laravel Migration Linter
on: [push, pull_request]
jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: 8.3 }
      - run: composer install --no-interaction
      - run: php artisan migrate:lint --json > lint-report.json

```
---

## 🧑‍💻 Contributing
Contributions are welcome!
If you have an idea for a useful rule or enhancement, feel free to open a PR or issue.

---

## 🧾 License
Released under the MIT License.
© 2025 Sufyan. All rights reserved.

---

## 🧩 2. Update `composer.json` Metadata  

Add under the root object:  

```json
"homepage": "https://github.com/sufyan/laravel-migration-linter",
"keywords": ["laravel", "migrations", "linter", "ci", "database", "schema"],
"support": {
    "issues": "https://github.com/sufyan/laravel-migration-linter/issues",
    "source": "https://github.com/sufyan/laravel-migration-linter"
}
```
---

## 🧠 Author
Muhammad Sufyan
📧 muhammadsufyanwebdeveloper@gmail.com
🐙 GitHub: @sufyan

“Smart developers don’t debug production — they lint migrations.”