---
id: rules
title: 📏 Available Linter Rules
sidebar_position: 5
---

Each rule can be enabled, disabled, or adjusted for severity in `config/migration-linter.php`.
Laravel Migration Linter analyzes your migration files and flags potential schema risks.
Below are the built-in linting rules, each with `rationale`, `triggers`, and `configuration` examples.

---

### ⚙️ Severity Levels

| Severity | Meaning | Typical Use |
|-----------|----------|--------------|
| `info` | Advisory only | Local development |
| `warning` | Risk detected, migration may succeed but unsafe | Default |
| `error` | Definite risk, migration should fail in CI | Production enforcement |

---

### Quick Navigation
- [AddNonNullableColumnWithoutDefault](#-addnonnullablecolumnwithoutdefault)
- [MissingIndexOnForeignKey](#-missingindexonforeignkey)
- [DropColumnWithoutBackup](#-dropcolumnwithoutbackup)
- [AddUniqueConstraintOnNonEmptyColumn](#-adduniqueconstraintonnonemptycolumn)
- [FloatColumnForMoney](#-floatcolumnformoney)

---

## 🧩 AddNonNullableColumnWithoutDefault

**Category:** Reliability / Safety  
**Default severity:** `warning`

---

### 🔍 What it checks
Warns when a **NOT NULL** column is added to an existing table **without** a default value.

On a table that already has data, adding a non-nullable column requires a default or a backfill step; otherwise the migration may fail or lock for a long time.

---

### 💣 Why it matters
- MySQL and PostgreSQL will try to fill existing rows with `NULL`, violating the NOT NULL constraint.  
- Large tables can experience long locks while the new column is materialized.  
- Production deploys may fail midway, leaving partially applied migrations.

---

### ⚠️ Triggers
```php
$table->string('email')->nullable(false);
$table->integer('quantity')->nullable(false);
```
✅ Safe alternatives
```php
// Option 1: Provide a sensible default
$table->string('email')->default('')->nullable(false);

// Option 2: Add as nullable, backfill, then alter
$table->string('email')->nullable();
DB::table('users')->update(['email' => '']);
$table->string('email')->nullable(false)->change();
```
⚙️ Configuration
```php
'AddNonNullableColumnWithoutDefault' => [
    'enabled'  => true,
    'severity' => 'warning', // change to 'error' to fail CI
],
```
🧾 Example output
```php
[warning] AddNonNullableColumnWithoutDefault  
→ Column 'email' on table 'users' is non-nullable without a default value.
```
### 🧠 Recommendation

- Use defaults or staged backfills when introducing NOT NULL columns on non-empty tables.

---

## 🧩 MissingIndexOnForeignKey

**Category:** Performance / Integrity  
**Default severity:** `warning`

---

### 🔍 What it checks
Detects likely foreign-key columns (those ending with `_id`) that are added **without** an index or foreign-key constraint.

---

### 💣 Why it matters
- Queries that join on the foreign-key column become slow without an index.  
- Deletes or updates on the parent table can lock the child table.  
- Missing indexes lead to poor query-planner choices and high I/O cost.

---

### ⚠️ Triggers
```php
$table->unsignedBigInteger('user_id');           // no index
$table->integer('account_id');                   // no index
$table->unsignedInteger('product_id');           // no FK
```
✅ Safe alternatives
```php
// add an index explicitly
$table->unsignedBigInteger('user_id');
$table->index('user_id');

// or use Laravel's helper (adds FK + index)
$table->foreignId('user_id')->constrained();

// composite key example
$table->foreign(['user_id', 'tenant_id'])->references(['id', 'id'])->on(['users', 'tenants']);
```
⚙️ Configuration
```php
'MissingIndexOnForeignKey' => [
    'enabled'  => true,
    'severity' => 'warning', // or 'error' to block CI
],
```
🧾 Example output
```bash
[warning] MissingIndexOnForeignKey  
→ Foreign key-like column 'user_id' on table 'orders' may be missing an index or constraint.
```
 ### 🧠 Recommendation
- Always index or constrain any column ending with _id.
- Prefer foreignId()->constrained() for clarity and safety.

---

## 🧩 DropColumnWithoutBackup

**Category:** Data Safety  
**Default severity:** `warning` *(consider setting to `error` in CI)*

---

### 🔍 What it checks
Warns whenever a migration **drops one or more columns** from a table without any indication of backup or confirmation.

---

### 💣 Why it matters
- Dropping a column permanently deletes its data.  
- There’s no easy rollback unless you restore from a database backup.  
- In production, this can lead to irreversible data loss and application crashes.

---

### ⚠️ Triggers
```php
$table->dropColumn('middle_name');

$table->dropColumn(['middle_name', 'nickname']);
```
✅ Safer approaches
```php
// 1️⃣ Rename instead of drop (keep data)
$table->renameColumn('middle_name', 'middle_name_old');

// 2️⃣ Back up data before dropping
DB::table('users')
  ->select('id', 'middle_name')
  ->whereNotNull('middle_name')
  ->orderBy('id')
  ->chunk(500, fn($rows) => Storage::disk('backups')->append('user_middle_names.csv', json_encode($rows)));

// 3️⃣ Drop later once verified
$table->dropColumn('middle_name_old');
```
⚙️ Configuration
```php
'DropColumnWithoutBackup' => [
    'enabled'  => true,
    'severity' => 'warning', // can be 'error' in strict mode
],
```
🧾 Example output
```bash
[warning] DropColumnWithoutBackup  
→ Dropping column 'middle_name' from table 'users' may result in data loss.
```
### 🧠 Recommendation
- Treat column drops as production-critical changes.
- Back up, rename, or stage removals to avoid losing user data.

---

## 🧩 AddUniqueConstraintOnNonEmptyColumn

**Category:** Data Integrity  
**Default severity:** `warning`

---

### 🔍 What it checks
Warns when a migration adds a **unique constraint** (either explicitly or inline) to a column that may already contain duplicate data.

---

### 💣 Why it matters
- If duplicates exist, the migration will **fail** and may roll back partially.  
- Even if it succeeds on empty tables, applying later on filled tables can break deploys.  
- Large tables with duplicates can cause long locks during constraint creation.

---

### ⚠️ Triggers
```php
// explicit unique index
$table->unique('email');

// composite unique
$table->unique(['email', 'tenant_id']);

// inline chained unique()
$table->string('email')->unique();
```
✅ Safer rollout pattern
```php

// 1️⃣ detect duplicates
$dupes = DB::table('users')
    ->select('email')
    ->groupBy('email')
    ->havingRaw('COUNT(*) > 1')
    ->get();

if ($dupes->isNotEmpty()) {
    // cleanup manually or script it
}

// 2️⃣ once deduped, safely add constraint
$table->unique('email');
```
⚙️ Configuration
```php
'AddUniqueConstraintOnNonEmptyColumn' => [
    'enabled'  => true,
    'severity' => 'warning', // can raise to 'error' for CI enforcement
],
```
🧾 Example output
```bash
[warning] AddUniqueConstraintOnNonEmptyColumn  
→ Adding unique constraint to 'email' may fail if duplicates already exist in 'users'.
```
### 🧠 Recommendation
Before adding unique constraints:
- Scan for and resolve duplicate values.
- Consider adding the constraint only after data cleanup migrations.
---

## 🧩 FloatColumnForMoney

**Category:** Precision / Data Integrity  
**Default severity:** `warning`

---

### 🔍 What it checks
Warns when a migration defines a **float** column for values that appear to represent **money or currency** (e.g., `price`, `amount`, `balance`, `total`, etc.).

---

### 💣 Why it matters
- Floating-point numbers can introduce rounding errors.  
- Financial calculations can become inconsistent across environments.  
- Use `decimal(p, s)` for precise and predictable storage.

---

### ⚠️ Triggers
```php
$table->float('price');
$table->float('amount');
$table->float('balance');
$table->float('total');
$table->float('tax');
```
✅ Recommended alternatives
```php
// use decimal with explicit precision and scale
$table->decimal('price', 10, 2);
$table->decimal('amount', 12, 4);

// or abstract it via a helper
$table->money('price'); // custom macro if defined
```
⚙️ Configuration
```php
'FloatColumnForMoney' => [
    'enabled'  => true,
    'severity' => 'warning', // can be 'error' for strict CI mode
],
```
🧾 Example output
```bash
[warning] FloatColumnForMoney  
→ Column 'price' in table 'orders' uses float(); consider decimal(10,2) for precise monetary values.
```
### 🧠 Recommendation
- Use decimal(p, s) (commonly decimal(10,2)) for any currency, cost, tax, or total columns.
- Reserve float() for scientific or approximate values only.

---