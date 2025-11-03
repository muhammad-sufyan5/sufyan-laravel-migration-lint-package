<p>
  <img src="assets/preview.png" alt="Laravel Migration Linter report" width="900">
</p>

# 🧩 Laravel Migration Linter  
[![Docs](https://img.shields.io/badge/docs-online-brightgreen?style=flat-square)](https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/)
[![Migration Linter](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/actions/workflows/migration-linter.yml/badge.svg)](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/sufyandev/laravel-migration-linter.svg?style=flat-square)](https://packagist.org/packages/sufyandev/laravel-migration-linter)
[![Total Downloads](https://img.shields.io/packagist/dt/sufyandev/laravel-migration-linter.svg?style=flat-square)](https://packagist.org/packages/sufyandev/laravel-migration-linter)
[![Laravel Version](https://img.shields.io/badge/Laravel-10%2B-orange?style=flat-square)](#)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue?style=flat-square)](#)
[![Version](https://img.shields.io/badge/version-v1.2.0-green?style=flat-square)](#)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)

A lightweight Laravel package that **analyzes your database migrations** and warns you about risky schema changes — before they reach production.  

---

## 🚀 Features
- 🔍 Detects dangerous migration patterns:
  - Non-nullable columns without defaults  
  - Missing indexes on foreign keys  
  - Unsafe column drops  
  - Risky unique constraints  
  - Floating-point money fields
- ⚙️ Configurable rule severities (`info`, `warning`, `error`)
- 🧠 Baseline support to ignore legacy issues
- 🧾 JSON or table output for CI/CD
- 🧩 Fully documented & tested (v1.2.0)

📘 **Read full rule docs:**  
👉 [https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/](https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/)

---

## 📦 Installation
Install via Composer:  
```bash
composer require sufyandev/laravel-migration-linter --dev
```
The package will auto-register via Laravel’s package discovery.

---

🧩 Usage
Run the built-in Artisan command to lint all migration files:

```bash
php artisan migrate:lint
```
Common options:

| Option                | Description                                |
| --------------------- | ------------------------------------------ |
| `--json`              | Output structured JSON (great for CI)      |
| `--path=`             | Lint a specific migration file/folder      |
| `--baseline`          | Use a custom baseline file                 |
| `--generate-baseline` | Create a baseline to ignore current issues |
| `--rules`             | View all rules and their enabled status    |
| `--summary`           | Display summary footer in output           |

Example Usage

### Lint all migrations
```bash
php artisan migrate:lint --json > storage/lint-report.json
```
### Generate a new baseline file (ignore current issues)
```bash
[warning] AddUniqueConstraintOnNonEmptyColumn
→ Adding unique constraint to 'email' may fail if duplicates exist in 'users'.

```

## ⚙️ Publishing Configuration

You can publish the configuration file to customize rule settings:

```bash
php artisan vendor:publish --tag="migration-linter-config"
```
`config/migration-linter.php`:

```bash
return [
    'severity_threshold' => 'warning',
    'rules' => [
        'AddNonNullableColumnWithoutDefault' => ['enabled' => true, 'severity' => 'warning'],
        'MissingIndexOnForeignKey'           => ['enabled' => true, 'severity' => 'warning'],
        // ...
    ],
];

```
## 🧾 CI/CD Integration (GitHub Actions)
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

## 💡What’s New in v1.2.0

- Composite & inline unique constraint detection
- `foreignId()->constrained()` & `morphs()` index checks
- Multi-column drop detection with `// safe drop` support
- `double()` /` real()` money column detection
- More expressive warnings & test coverage

---

## 🧑‍💻 Contributing
Contributions are welcome!
If you have an idea for a useful rule or enhancement, feel free to open a PR or issue.

---

## 🧾 License
Released under the MIT License.
© 2025 Sufyan. All rights reserved.

---

## 🧠 Author
Muhammad Sufyan
📧 muhammadsufyanwebdeveloper@gmail.com
🐙 GitHub: @muhammad-sufyan5

“Smart developers don’t debug production — they lint migrations.”

📘 **Full Documentation:**  
👉 [https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/](https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/)
