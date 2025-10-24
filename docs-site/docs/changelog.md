---
id: changelog
title: 🗓️ Changelog
sidebar_position: 8
---

## 🧾 Changelog

All notable changes to this project will be documented here.

---

### [1.1.0] – 2025-10-21

#### 🆕 Added
- `DropColumnWithoutBackup` rule — warns when columns are dropped without confirmation.
- `AddUniqueConstraintOnNonEmptyColumn` rule — warns when adding unique constraints that might fail on existing data.
- `FloatColumnForMoney` rule — warns when using `float()` for monetary fields; recommends `decimal(10,2)` instead.

#### ⚙️ Improved
- Output formatting improvements for smaller terminals.

---

### [1.0.0] – 2025-10-15

#### 🆕 Added
- Core engine and command:  
```bash
  php artisan migrate:lint
```
Base rules:
`AddNonNullableColumnWithoutDefault`, `MissingIndexOnForeignKey`.

Config publishing, baseline ignoring, JSON report output.