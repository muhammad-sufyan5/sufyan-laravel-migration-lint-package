---
id: installation
title: ⚙️ Installation
sidebar_position: 2
---

Install **Laravel Migration Linter** via Composer:

```bash
composer require sufyandev/laravel-migration-linter --dev
```
💡 It’s recommended to install it as a development dependency since it’s used primarily during code review and CI, not in production runtime.

## 🔧 Package Registration
The package will auto-register through Laravel’s package discovery, so no manual provider entry is required.
After installation, you’re ready to lint your migrations immediately.

If you prefer, you can verify the service provider is loaded:
```bash
php artisan list | grep lint
```
You should see:
```bash
migrate:lint  Statistically analyze migration files for risky schema changes
```

## ⚙️ Publish Configuration (optional)

If you’d like to customize rule severities or enable/disable specific rules:
```bash
php artisan vendor:publish --tag="migration-linter-config"
```
This will create:
```bash
config/migration-linter.php
```
You can then adjust individual rules or add your own custom ones (see [🧠 Writing Custom Rules](./writing-custom-rules.md)).

## ▶️ Run the Linter

Use the built-in Artisan command to analyze your migrations:
```bash
php artisan migrate:lint
```
Example output:
```bash
🔍 Running Laravel Migration Linter...

⚠️  Lint Report
[warning] AddNonNullableColumnWithoutDefault
→ Column 'email' on table 'users' is non-nullable without a default value.
```

## 🧭 Next Steps

- 🧩 [Usage](./usage.md) — See command options and examples  
- ⚙️ [Configuration](./configuration.md) — Customize rule severities  
- 🧠 [Writing Custom Rules](./writing-custom-rules.md) — Create your own organization-specific checks  
- 🤖 [CI/CD Integration](./ci-cd.md) — Enforce linting in automated pipelines  

---