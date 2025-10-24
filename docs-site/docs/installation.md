---
id: installation
title: ⚙️ Installation
sidebar_position: 2
---

## 📦 Installation

Install via Composer:

```bash
composer require sufyandev/laravel-migration-linter --dev
```
The package will auto-register via Laravel’s package discovery.

▶️ Run the Linter

Use the built-in Artisan command to lint all migrations:
```bash
php artisan migrate:lint
```