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
| `--html=`             | Generate an interactive HTML report. If no path specified, defaults to `storage/app/migration-lint-report.html`.                                                                               |
| `--baseline=`         | Provide a custom path to a baseline file (overrides the default baseline file).                                                                                                                 |
| `--compact`           | Display a shorter, condensed table layout for smaller terminals or narrow CI logs.                                                                                                              |
| `--rules`             | View all rules and their enabled statuses.                                                                                                                                                      |
| `--summary`           | Display summary footer in output.                                                                                                                                                               |
| `--no-suggestions`    | Hide migration suggestions from output (show only the warnings table).                                                                                                                          |

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
    "rule": "DropColumnWithoutBackup",
    "severity": "warning",
    "message": "Dropping column 'age' from table 'users' may result in data loss.",
    "file": "2025_10_20_123456_update_users_table.php",
    "line": 15,
    "suggestion": "Back up data before dropping. Consider renaming with suffix '_old'.",
    "docs_url": "https://...docs/rules#-dropcolumnwithoutbackup"
  }
]
```
**Note:** JSON output now includes `suggestion` and `docs_url` fields for easy integration with CI/CD tools.
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
Example output includes a summary section with total files scanned, issue counts by severity, and overall status.

---

### 🎨 Hide Suggestions (cleaner output)
```bash
php artisan migrate:lint --no-suggestions
```
This displays only the warnings table without the detailed migration suggestions. Useful when you're familiar with the issues and want a compact view.

**Combine flags for customized output:**
```bash
# Table only (no suggestions, no summary)
php artisan migrate:lint --no-suggestions

# Table + summary (no suggestions)
php artisan migrate:lint --summary --no-suggestions

# Full report with everything (default)
php artisan migrate:lint --summary
```

**Note:** Suggestions are grouped by rule type to avoid repetition. If you have multiple violations of the same rule, the suggestion is shown only once with an occurrence count.

---

### 📊 Example Output (with grouped suggestions)
```bash
php artisan migrate:lint --summary
```
Example output:
```bash
⚠️  Lint Report

+--------------------------------+-------------------------------+--------+----------+--------------------------------+
| File                           | Rule                          | Column | Severity | Message                        |
+--------------------------------+-------------------------------+--------+----------+--------------------------------+
| 2024_01_15_create_orders.php   | ChangeColumnTypeOnLargeTable  | price  | error    | Changing column type with ->c… |
| 2024_01_15_create_orders.php   | ChangeColumnTypeOnLargeTable  | status | error    | Changing column type with ->c… |
| 2024_01_20_rename_user.php     | RenamingColumnWithoutIndex    | name   | warning  | Renaming column 'old_name' to… |
+--------------------------------+-------------------------------+--------+----------+--------------------------------+

💡 Suggestions & Recommendations
════════════════════════════════════════════════════════════

📋 ChangeColumnTypeOnLargeTable (2 occurrences)
────────────────────────────────────────────────────────────
   **Recommended Migration Strategy:**
   
   ✅ **Option 1: Zero-Downtime Multi-Step Migration**
   [Detailed migration steps with code examples...]
   
   ⚠️ **Option 2: Maintenance Window**
   [Alternative approach...]
   
   📖 Documentation: https://docs.example.com/...

📋 RenamingColumnWithoutIndex (1 occurrence)
────────────────────────────────────────────────────────────
   [3-phase zero-downtime strategy...]
   📖 Documentation: https://docs.example.com/...

📊 Summary
───────────────────────────────────
🧩 Total Files Scanned:     3
🔍 Issues Found:            3
⚠️  Warnings:               1
❌ Errors:                  2
💡 Info:                    0

 [WARNING] ⚠️  Some migrations contain potential risks.
```

**Key Features:**
- **Grouped Suggestions** — Same rule violations show suggestion only once with occurrence count
- **Visual Hierarchy** — Clear sections with proper separators (═ and ─)
- **Color Coding** — Cyan for rules, blue for docs, severity-based colors
- **Minimal Repetition** — Clean, professional output

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

## � Understanding Suggestions

Each warning includes **actionable suggestions** to help you fix the issue. When you run `php artisan migrate:lint`, you'll see:

1. **The Warning Table** — Shows all issues with severity and details
2. **Suggestions Section** — Detailed fix recommendations below the table

Example:
```
⚠️  Lint Report
┌──────────────┬───────────────────────────┬────────┬──────────┬─────────────┐
│ File         │ Rule                      │ Column │ Severity │ Message     │
├──────────────┼───────────────────────────┼────────┼──────────┼─────────────┤
│ create_users │ AddNonNullableColumnWi... │ email  │ warning  │ Adding NOT  │
│              │                           │        │          │ NULL...     │
└──────────────┴───────────────────────────┴────────┴──────────┴─────────────┘

[Suggestion #1] AddNonNullableColumnWithoutDefault:
  Option 1: Add a default value:
    $table->string('email')->default('')->nullable(false);
  
  Option 2: Make it nullable, then alter:
    $table->string('email')->nullable();
    DB::table('users')->update(['email' => '...']);
    $table->string('email')->nullable(false)->change();
  
  📖 Learn more: https://...docs/rules#-addnonnullablecolumnwithoutdefault
```

**For JSON output**, suggestions are included in each issue object for programmatic access.

---

## �💡 Pro Tips

- Use --generate-baseline once when introducing the linter to a legacy codebase, then commit the baseline file.
- Regularly re-run php artisan migrate:lint in your CI/CD to catch unsafe schema changes early.
- Combine with your existing testing jobs to prevent migration issues from reaching production.
- **Each warning includes actionable suggestions** — follow them to fix issues quickly.
- **JSON output includes suggestions and documentation links** — integrate with your tools!

✅ That's it!
You're ready to lint, baseline, and enforce migration safety across all environments.

---