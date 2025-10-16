# 🧱 Laravel Migration Linter

A lightweight **Laravel developer tool** by **Sufyan** that analyzes your database migration files and highlights **risky schema changes**, **missing indexes**, or **non-best-practice patterns** — before they hit production.  

> Catch potential database issues early. Make your migrations bulletproof.

---

## 🚀 Installation

Require the package using Composer (recommended for local/dev environments):

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
🧩 Usage
Run the built-in Artisan command to lint all migration files:

```bash
php artisan migrate:lint
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
⚙️ Configuration Options
You can configure which rules are enabled and what severity level they should have:

```bash
// config/migration-linter.php

return [
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

    // Optional: Severity threshold for CI exit codes
    'severity_threshold' => 'warning',

    // Optional: Future use — baseline file path
    'baseline_path' => storage_path('migration-linter-baseline.json'),
];
```
## 🧩 Available Severities

- **info** — For minor notices or suggestions.  
- **warning** — For risky or potentially inefficient schema changes.  
- **error** — For dangerous changes (e.g., dropping tables, adding NOT NULL columns on large tables).

---

| Rule ID                              | Description                                                                               | Default Severity |
| ------------------------------------ | ----------------------------------------------------------------------------------------- | ---------------- |
| `AddNonNullableColumnWithoutDefault` | Detects adding NOT NULL columns without default values (risky for existing large tables). | ⚠️ warning       |
| `MissingIndexOnForeignKey`           | Detects foreign key-like columns (`*_id`) missing indexes or constraints.                 | ⚠️ warning       |

---

## ⚙️ Advanced Features (Upcoming)

| Feature | Description |
| -------- | ------------ |
| **Baseline Ignoring System** | Generate and apply a baseline to ignore known issues while tracking new ones. |
| **Custom Rules** | Extend and register your own linting rules. |
| **CI/CD Integration** | Use exit codes and JSON output to integrate into automated pipelines. |

---

## 🧠 Example CI/CD Integration

Run the linter as part of your pipeline:

```yaml
# GitHub Actions Example
- name: Run Laravel Migration Linter
  run: php artisan migrate:lint --json > lint-report.json
  ```
CI will fail automatically if issues meet or exceed the configured severity threshold.

💡 Why Use This?
✅ Prevents dangerous schema changes before deployment.
⚙️ Enforces database best practices automatically.
🧩 Extensible — you can create custom rules for your project.
🚀 Safe for legacy projects (upcoming baseline feature).

---

🧑‍💻 Contributing
Contributions are welcome!
If you have an idea for a useful rule or enhancement, feel free to open a PR or issue.

Fork the repository.

Create a feature branch.

Commit your changes and open a pull request.

---

🧾 License
This package is open-source software licensed under the MIT license.

---

🧠 Author
Muhammad Sufyan
📧 muhammadsufyanwebdeveloper@gmail.com
🐙 GitHub: @sufyan

“Smart developers don’t debug production — they lint migrations.”