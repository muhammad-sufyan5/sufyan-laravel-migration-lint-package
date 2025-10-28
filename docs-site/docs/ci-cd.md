---
id: ci-cd
title: 🤖 CI/CD Integration
sidebar_position: 7
---

Laravel Migration Linter integrates seamlessly with **GitHub Actions**, **GitLab CI**, and other continuous integration pipelines.  
It ensures no unsafe schema changes reach production by failing builds when risky migrations are detected.

---

## 🧰 GitHub Actions Example

```yaml
name: Laravel Migration Linter

on:
  push:
    branches: [ main, master ]
  pull_request:
    branches: [ main, master ]

jobs:
  lint-migrations:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          extensions: mbstring, pdo, bcmath, curl, dom, xml
          tools: composer:v2

      # 🧩 Example: setting up a test Laravel project using your local package
      - run: composer create-project laravel/laravel test-laravel-app --quiet

      - name: Install Laravel Migration Linter (local path)
        run: |
          cd test-laravel-app
          composer config repositories.sufyandev path ../
          composer require sufyandev/laravel-migration-linter:@dev --no-interaction

      - name: Run Migration Linter
        run: |
          cd test-laravel-app
          php artisan migrate:lint --json > lint-report.json

```
---

## 🧪 Example in CI Console
```sql
🔍 Running Laravel Migration Linter...

⚠️  Lint Report
+-------------------------------------+-------------------------------------------+-----------+----------+----------------------------------------------------------+
| File                                | Rule                                      | Column    | Severity | Message                                                  |
+-------------------------------------+-------------------------------------------+-----------+----------+----------------------------------------------------------+
| 0001_01_01_000000_create_users.php  | AddNonNullableColumnWithoutDefault        | email     | warning  | Adding NOT NULL column without default value.           |
| 2025_10_15_000000_create_orders.php | FloatColumnForMoney                       | price     | warning  | Using float() for price — use decimal(10,2) instead.     |
+-------------------------------------+-------------------------------------------+-----------+----------+----------------------------------------------------------+
```
## 📊 Exit Codes and Build Behavior

| Severity Threshold | Description                           | Exit Code | Build Result |
| ------------------ | ------------------------------------- | --------- | ------------ |
| `info`             | Advisory messages only                | 0         | ✅ Pass       |
| `warning`          | Potential issues found, not fatal     | 0         | ✅ Pass       |
| `error`            | Migration-breaking or data-loss risks | 1         | ❌ Fail       |

You can adjust this threshold via config/migration-linter.php:
```php
'severity_threshold' => 'error',
```
When set to 'error', only error-level issues cause your CI pipeline to fail.

---

## 🧠 Tips for CI/CD Integration

- 💾 Commit the baseline file (migration-linter-baseline.json) once generated.
- This prevents known legacy issues from reappearing in reports.
- 🧩 Use --json output to generate structured reports for custom dashboards.
- You can also upload the JSON artifact for analysis.
- 🚦 Fail fast: set 'severity_threshold' => 'error' in config to stop deployments on risky migrations.
- 🔁 Run on every PR — this ensures every migration is linted before merge.

---

## 🧾 GitLab CI Example
If you're using GitLab, add a stage like this:
```yml
stages:
  - lint

lint_migrations:
  stage: lint
  image: php:8.3
  script:
    - apt-get update && apt-get install -y unzip git
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install --no-interaction
    - php artisan migrate:lint --json > lint-report.json
```
✅ Result:
Your CI/CD pipeline now automatically enforces safe, production-ready migrations — blocking schema-breaking changes before they reach your main branch.

---