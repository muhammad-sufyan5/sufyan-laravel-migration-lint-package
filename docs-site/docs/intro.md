---
id: intro
title: Introduction
slug: /         # 👈 this tells Docusaurus it's the homepage
sidebar_position: 1
---

# 🧩 Laravel Migration Linter

[![Migration Linter](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/actions/workflows/migration-linter.yml/badge.svg)](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/sufyandev/laravel-migration-linter.svg?style=flat-square)](https://packagist.org/packages/sufyandev/laravel-migration-linter)
[![Total Downloads](https://img.shields.io/packagist/dt/sufyandev/laravel-migration-linter.svg?style=flat-square)](https://packagist.org/packages/sufyandev/laravel-migration-linter)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](https://github.com/muhammad-sufyan5/sufyan-laravel-migration-lint-package/blob/main/LICENSE)


A lightweight Laravel package that **analyzes your database migrations** and warns you about risky schema changes — before they reach production.

---

## 🚀 Features

✅ Detects dangerous migration operations (like adding non-nullable columns without defaults).  
✅ Warns about missing indexes on foreign key columns.  
✅ Warns when columns are dropped (data loss risk).  
✅ Warns when `float()` is used for money fields (precision issue).  
✅ Warns when adding unique constraints to existing data.  
✅ Configurable rule severities (info, warning, error).  
✅ Baseline support to ignore known legacy issues.  
✅ CLI report with JSON output & colorized table.  
✅ Ready for CI/CD integration (GitHub Actions support).

---

## 📸 Screenshot

<img src="img/migrate-lint-report.png" alt="Laravel Migration Linter report" width="900" />

<sub>Screenshot from v1.0.0</sub>
