---
id: usage
title: 🧩 Usage
sidebar_position: 3
---

After installing the package, run the linter on all migrations:

```bash
php artisan migrate:lint
```
This scans your `database/migrations` folder for risky schema operations and reports potential issues.

---

## ⚙️ Command Options
You can customize the linting behavior using the following flags and options:
| Option / Flag         | Description                                                                                                                                                                                     |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `--generate-baseline` | Create a baseline JSON file (`migration-linter-baseline.json`) that records current issues, allowing you to ignore them in future runs (useful when introducing the linter to legacy projects). |
| `--path=`             | Lint a specific file or directory instead of the default `database/migrations` folder.                                                                                                          |
| `--json`              | Output results in structured JSON format (ideal for CI/CD or automation).                                                                                                                       |
| `--baseline=`         | Provide a custom path to a baseline file (overrides the default baseline file).                                                                                                                 |
| `--compact`           | Display a shorter, condensed table layout for smaller terminals or narrow CI logs.                                                                                                              |
| `--rules`           | View all rules and their enabled statuses.                                                |
| `--summary`           | Display summary footer in output.                                              |

---

## 💻 Example Commands

### 🔍 Lint all migrations
```bash
php artisan migrate:lint
```
### 🧾 Generate a new baseline file (ignore current issues)
```bash
php artisan migrate:lint --generate-baseline
```
This creates `migration-linter-baseline.json` in your project root.

---

### 📂 Lint a specific path
```bash
php artisan migrate:lint --path=database/migrations/2025_10_20_123456_create_users_table.php
```

---

### 🧠 Export report as JSON (for CI pipelines)
```bash
php artisan migrate:lint --json > storage/lint-report.json
```
Example output (simplified):
```bash
[
  {
    "ruleId": "DropColumnWithoutBackup",
    "severity": "warning",
    "message": "Dropping column 'age' from table 'users' may result in data loss.",
    "file": "2025_10_20_123456_update_users_table.php"
  }
]
```
---

### 🗂 Use a custom baseline file
```bash
php artisan migrate:lint --baseline=storage/custom-baseline.json
```
---

### 🗂 List all rules with their enabled statuses
```bash
php artisan migrate:lint --rules
```
Example output:
```bash
📋 Available Migration Linter Rules

+--------------------------------------------+----------+---------------------------------------------------------------+
| Rule ID                                   | Enabled  | Description                                                   |
+--------------------------------------------+----------+---------------------------------------------------------------+
| AddNonNullableColumnWithoutDefault        | Yes   | Warns when adding a NOT NULL column without a default value.  |
| MissingIndexOnForeignKey                  | No    | Detects missing indexes on foreign key columns.               |
| DropColumnWithoutBackup                   | Yes   | Warns when columns are dropped without backup.                |
| AddUniqueConstraintOnNonEmptyColumn       | No    | Warns when adding unique constraints on existing data.        |
| FloatColumnForMoney                       | Yes   | Warns when float() used for monetary values.                  |
+--------------------------------------------+----------+---------------------------------------------------------------+
```

---

### 🗂 Display Summary in output
```bash
php artisan migrate:lint --summary
```
Example output:
```bash
� Summary
───────────────────────────────────
� Total Files Scanned:     6
� Issues Found:            15
⚠️  Warnings:              15
❌ Errors:                 0
� Info:                    0


 [WARNING] ⚠️  Some migrations contain potential risks.

```

---

### 🖥 Compact mode for smaller terminals
```bash
php artisan migrate:lint --compact
```
This outputs a shorter version of the table without verbose file paths.

---

## 🚦Exit Codes

| Exit Code | Meaning                                                 | When it occurs    |
| --------- | ------------------------------------------------------- | ----------------- |
| `0`       | ✅ No critical issues (or only warnings below threshold) | Safe to proceed   |
| `1`       | ❌ One or more rules exceeded the severity threshold     | CI/CD should fail |

The threshold is controlled by severity_threshold in your configuration file.
For example, setting 'severity_threshold' => 'error' will fail the command only when error-level issues are found.

---

## 💡 Pro Tips

- Use --generate-baseline once when introducing the linter to a legacy codebase, then commit the baseline file.
- Regularly re-run php artisan migrate:lint in your CI/CD to catch unsafe schema changes early.
- Combine with your existing testing jobs to prevent migration issues from reaching production.

✅ That’s it!
You’re ready to lint, baseline, and enforce migration safety across all environments.

---