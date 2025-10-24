---
id: rules
title: 📏 Available Linter Rules
sidebar_position: 5
---

## 🧩 Available Linter Rules

This package currently ships with five major rules.  
Each rule can be enabled, disabled, or adjusted for severity in `config/migration-linter.php`.

---

### 1️⃣ **AddNonNullableColumnWithoutDefault**

Warns when adding a `NOT NULL` column without a default value to existing tables.  
**Severity:** `warning`

---

### 2️⃣ **MissingIndexOnForeignKey**

Detects when a foreign key column (like `user_id`, `order_id`) lacks an index.  
**Severity:** `warning`

---

### 3️⃣ **DropColumnWithoutBackup**

Warns when columns are dropped without explicit confirmation or backup.  
**Severity:** `warning`

---

### 4️⃣ **AddUniqueConstraintOnNonEmptyColumn**

Warns when adding unique constraints that could fail on existing duplicate data.  
**Severity:** `warning`

---

### 5️⃣ **FloatColumnForMoney**

Warns when `float()` is used for money-like columns (e.g., `price`, `amount`, `cost`).  
💡 **Recommendation:** Use `decimal(10,2)` instead.  
**Severity:** `warning`
