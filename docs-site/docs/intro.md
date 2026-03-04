---
id: intro
title: Introduction
slug: /           # 👈 makes this the homepage
sidebar_position: 1
---

# 🧩 Laravel Migration Linter

[![Migration Linter](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/actions/workflows/migration-linter.yml/badge.svg)](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/sufyandev/laravel-migration-linter.svg?style=flat-square)](https://packagist.org/packages/sufyandev/laravel-migration-linter)
[![Total Downloads](https://img.shields.io/packagist/dt/sufyandev/laravel-migration-linter.svg?style=flat-square)](https://packagist.org/packages/sufyandev/laravel-migration-linter)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/blob/main/LICENSE)

> “Smart developers don’t debug production — they lint migrations.”

---

## 💡 Overview

**Laravel Migration Linter** is a lightweight developer tool that automatically scans your database migrations for **risky schema changes** before they hit production.

It acts as a **static analyzer** for your migrations — preventing downtime, data loss, or broken deploys by catching unsafe patterns early in your CI/CD pipeline.

---

## 🚀 Key Features

- 🧠 **Safety-first linting** — Detects dangerous operations like adding non-nullable columns without defaults or dropping columns.  
- ⚡ **Performance awareness** — Warns when missing indexes, column renames, or inefficient data types can impact query speed.  
- 💾 **Data integrity checks** — Alerts when unique constraints, float columns, or type changes may corrupt data.
- 🔒 **Downtime prevention** — Detects column renames and operations that cause table locks on production databases.
- 🧩 **Configurable rules** — Enable, disable, or adjust severity (`info`, `warning`, `error`) per project.  
- 🧱 **Baseline support** — Ignore known legacy issues and focus only on new violations.  
- 📊 **Developer-friendly reports** — Colorized console output, JSON export, and compact mode.  
- 🤖 **CI/CD ready** — Integrates cleanly with GitHub Actions, GitLab, and other pipelines.
- 🏗️ **SOLID Architecture** (v2.0.0) — Extensible with dependency injection, custom formatters, and services.  
- 💡 **Actionable Suggestions** — Every warning includes fix recommendations and documentation links.

---

## 🧰 Why Use It

Traditional Laravel migrations execute directly on production data — even small mistakes can cause downtime.  
**Laravel Migration Linter** analyzes migrations *before* they run, letting you:

- Prevent table locks and schema conflicts  
- Detect destructive operations in pull requests  
- Enforce safe migration practices across teams  
- Standardize schema evolution in CI/CD workflows  

---

## 📸 Example Reports

<!-- <img src="img/migrate-lint-reportv2.1.0.png" alt="Laravel Migration Linter report" width="900" /> -->
<img src="img/html-reportv2.1.2.png" alt="Laravel Migration Linter HTML Report" width="900" />

<sub>Screenshot from v2.1.2 — Interactive HTML report with charts, filtering, and search functionality</sub>

---

<img src="img/migrate-lint-summaryv2.1.0.png" alt="Laravel Migration Linter report" width="900" />

<sub>Screenshot from v2.1.0 — showing lint warnings with flag `check_all_tables => true` in console output</sub>

---

## 🧭 Next Steps

- 📦 [Installation Guide](./installation.md) — Learn how to install and publish config  
- 🧩 [Usage](./usage.md) — See command options and examples  
- ⚙️ [Configuration](./configuration.md) — Customize rule severities  
- 🧠 [Writing Custom Rules](./writing-custom-rules.md) — Create your own organization-specific checks  
- 🤖 [CI/CD Integration](./ci-cd.md) — Enforce linting in automated pipelines  

---

© 2025 **Sufyan** — Released under the [MIT License](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/blob/main/LICENSE).
