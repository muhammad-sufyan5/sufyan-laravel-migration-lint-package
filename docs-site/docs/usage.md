---
id: usage
title: 🧩 Usage
sidebar_position: 3
---

## ▶️ Basic Usage

After installing the package, lint your migrations with:

```bash
php artisan migrate:lint
```
### You can use the following flags and options to customize behavior:

| Option / Flag         | Description                                                                    |
| --------------------- | ------------------------------------------------------------------------------ |
| `--generate-baseline` | Create a JSON file to skip known existing issues (useful for legacy projects). |
| `--path=`             | Lint a specific file or directory instead of `database/migrations`.            |
| `--json`              | Output structured JSON (useful for CI/CD integration).                         |
| `--baseline=`         | Provide a custom baseline file path (overrides the default).                   |
| `--compact`           | Output shorter table view for smaller terminals.                               |
                                                |


Example Usage

### Lint all migrations
```bash
php artisan migrate:lint
```
### Generate a new baseline file (ignore current issues)
```bash
php artisan migrate:lint --generate-baseline
```
### Run only on a specific path
```bash
php artisan migrate:lint --path=database/migrations
```
### Export lint report as JSON (for CI)
```bash
php artisan migrate:lint --json > storage/lint-report.json
```
### Use a custom baseline file
```bash
php artisan migrate:lint --baseline=storage/custom-baseline.json
```
### Improving usability for smaller terminal sizes
```bash
php artisan migrate:lint --compact
```