---
id: ci-cd
title: 🤖 CI/CD Integration
sidebar_position: 7
---

## ⚙️ CI/CD Integration

Laravel Migration Linter works perfectly inside **GitHub Actions**, **GitLab CI**, and other automation pipelines.

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
      - run: composer create-project laravel/laravel test-laravel-app --quiet
      - run: |
          cd test-laravel-app
          composer config repositories.sufyandev path ../
          composer require sufyandev/laravel-migration-linter:@dev --no-interaction
          php artisan migrate:lint --json > lint-report.json
```
## 🧪 Example in CI Console
🔍 Running Laravel Migration Linter...

⚠️  Lint Report
+-------------------------------------+-------------------------------------------+-----------+----------+----------------------------------------------------------+
| File                                | Rule                                      | Column    | Severity | Message                                                  |
+-------------------------------------+-------------------------------------------+-----------+----------+----------------------------------------------------------+
| 0001_01_01_000000_create_users.php  | AddNonNullableColumnWithoutDefault        | email     | warning  | Adding NOT NULL column without default value.           |
| 2025_10_15_000000_create_orders.php | FloatColumnForMoney                       | price     | warning  | Using float() for price — use decimal(10,2) instead.     |
+-------------------------------------+-------------------------------------------+-----------+----------+----------------------------------------------------------+
