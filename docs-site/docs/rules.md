---
id: rules
title: 📏 Available Linter Rules
sidebar_position: 5
---

Each rule can be enabled, disabled, or adjusted for severity in `config/migration-linter.php`.
Laravel Migration Linter analyzes your migration files and flags potential schema risks.
Below are the built-in linting rules, each with `rationale`, `triggers`, and `configuration` examples.

💡 **Note:** Each rule now includes **actionable suggestions** and documentation links in its output.
When the linter detects an issue, it provides specific steps to fix it and links to relevant docs.

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
- [SoftDeletesOnProduction](#-softdeletesonproduction)

---

## 🧩 AddNonNullableColumnWithoutDefault

**Category:** Reliability / Safety  
**Default severity:** `warning`

---

### 🔍 What it checks
Warns when a **NOT NULL** column is added to an existing table **without** a default value.

On a table that already contains data, adding or altering a non-nullable column requires a default or a backfill step; otherwise the migration may fail or cause long-running locks.

The rule now also detects:

- `->change()` calls that modify existing columns to NOT NULL.
- Automatically skips `Schema::create()` migrations (new tables).
- Honors config keys `large_table_names` and `check_all_tables`.

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
Additional global options:
```bash
'large_table_names' => ['users', 'orders'],  // Only check these if check_all_tables = false
'check_all_tables'  => true,                 // Lint all tables by default
```
🧾 Example output
```php
[warning] AddNonNullableColumnWithoutDefault  
→ Column 'email' on table 'users' is non-nullable without a default value.
```
### 🧠 Recommendation

- Always provide a default value when adding NOT NULL columns.
- When modifying existing columns, use a two-step migration:
    - Add or backfill data.
    - Then enforce the NOT NULL constraint.
- Skip new-table checks since Schema::create() is safe.

---

## 🧩 MissingIndexOnForeignKey

**Category:** Performance / Integrity  
**Default severity:** `warning`

---

### 🔍 What it checks
Warns when foreign key–like columns are added without an index or foreign-key constraint.

The rule now detects:

- `foreignId('user_id')` without `->constrained()`
- `morphs()` or `nullableMorphs()` without `->index()`
- Composite `foreign([...])` keys without an accompanying `index([...])`
- (Legacy `_id` heuristic removed for better accuracy)

---

### 💣 Why it matters
- Queries that join on the foreign-key column become slow without an index.  
- Deletes or updates on the parent table can lock the child table.  
- Missing indexes lead to poor query-planner choices and high I/O cost.
- Composite keys without indexes can lead to full-table scans.

---

### ⚠️ Triggers
```php
// 🚫 No ->constrained() — adds column but not FK or index
$table->foreignId('user_id');

// 🚫 No ->index() on polymorphic relation
$table->morphs('taggable');

// 🚫 Composite foreign key without index
$table->foreign(['user_id', 'tenant_id'])
      ->references(['id', 'id'])
      ->on('users');

```
✅ Safe alternatives
```php
// ✅ add explicit constraint
$table->foreignId('user_id')->constrained();

// ✅ add index to polymorphic relation
$table->morphs('taggable');
$table->index(['taggable_id', 'taggable_type']);

// ✅ composite FK with matching index
$table->foreign(['user_id', 'tenant_id'])
      ->references(['id', 'id'])
      ->on('users');
$table->index(['user_id', 'tenant_id']);

```
⚙️ Configuration
```php
'MissingIndexOnForeignKey' => [
    'enabled'  => true,
    'severity' => 'warning', // or 'error' to block CI
    // 🧩 Feature toggles
    'check_foreign_id_without_constrained' => true,
    'check_morphs_without_index'           => true,
    'check_composite_foreign'              => true,
],
```
🧾 Example output
```bash
[warning] MissingIndexOnForeignKey  
→ Column 'user_id' on table 'orders' uses foreignId() but has no ->constrained(); constraint or index may be missing.

[warning] MissingIndexOnForeignKey  
→ Polymorphic relation 'taggable' on table 'tags' has no index; consider adding ->index() for faster lookups.

```
 ### 🧠 Recommendation

- Always use     instead of manual `unsignedBigInteger().`
- Index all polymorphic relations (`morphs`, `nullableMorphs`).
- Add composite indexes for multi-column foreign keys.
- Keep this rule enabled in CI to prevent slow queries and orphaned data.

---

## 🧩 DropColumnWithoutBackup

**Category:** Data Safety  
**Default severity:** `warning` *(consider setting to `error` in CI)*

---

### 🔍 What it checks
Warns whenever a migration **drops one or more columns** from a table without any indication of backup or confirmation.

The rule now also:

- Detects multiple-column drops such as `dropColumn(['a','b'])`.
- Skips warnings if the migration line includes a “safe-drop” comment, e.g. `// safe drop `or `/* safe-drop */`.

---

### 💣 Why it matters
- Dropping a column permanently deletes data — no rollback can recover it.
- Production databases often require a backup or rename step before deletion.
- Irreversible drops can break reports, analytics, or legacy code paths.

---

### ⚠️ Triggers
```php
// 🚫 Single column drop
$table->dropColumn('middle_name');

// 🚫 Multiple column drop
$table->dropColumn(['middle_name', 'nickname']);

```
✅ Safer approaches
```php
// ✅ 1. Rename instead of drop (keep data)
$table->renameColumn('middle_name', 'middle_name_old');

// ✅ 2. Back up before dropping
DB::table('users')
  ->select('id', 'middle_name')
  ->whereNotNull('middle_name')
  ->chunk(500, fn ($rows) =>
      Storage::disk('backups')->append('user_middle_names.csv', json_encode($rows))
  );

// ✅ 3. Confirm safe drop explicitly
$table->dropColumn(['middle_name', 'nickname']); // safe drop

```
⚙️ Configuration
```php
'DropColumnWithoutBackup' => [
    'enabled'  => true,
    'severity' => 'warning', // can be 'error' in strict mode
    'allow_safe_comment' => true, // skip warnings for "// safe drop" comments
],
```
🧾 Example output
```bash
[warning] DropColumnWithoutBackup
→ Dropping multiple columns ('middle_name', 'nickname') from table 'users' may result in data loss.
```
### 🧠 Recommendation
- Treat column drops as production-critical operations.
- Always back up or rename before deletion.
- Use `// safe drop` comments to explicitly acknowledge intentional removals.
- In CI, set severity = `error` to prevent accidental data loss migrations.

---

## 🧩 AddUniqueConstraintOnNonEmptyColumn

**Category:** Data Integrity  
**Default severity:** `warning`

---

### 🔍 What it checks
Warns when a migration adds a **unique constraint** (either explicitly or inline) to a column that may already contain duplicate data.

The rule now detects:

- Explicit `$table->unique('column')`
- Composite` $table->unique(['col1','col2'])`
- Inline `->unique()` calls on column definitions
- Inline `->unique()->change()` calls that modify existing columns
- Optional config flag to disable composite detection

---

### 💣 Why it matters
- If duplicates exist, the migration will **fail** and may roll back partially.  
- Even if it succeeds on empty tables, applying later on filled tables can break deploys.  
- Large tables with duplicates can cause long locks during constraint creation.

---

### ⚠️ Triggers
```php
// 🚫 explicit unique constraint
$table->unique('email');

// 🚫 composite unique constraint
$table->unique(['email', 'tenant_id']);

// 🚫 inline unique definition
$table->string('email')->unique();

// 🚫 inline unique with change() on existing column
$table->string('username')->unique()->change();

```
✅ Safer rollout pattern
```php
// 1️⃣ Detect duplicates
$dupes = DB::table('users')
    ->select('email')
    ->groupBy('email')
    ->havingRaw('COUNT(*) > 1')
    ->get();

if ($dupes->isNotEmpty()) {
    // cleanup manually or via script before migration
}

// 2️⃣ Once deduped, safely add constraint
$table->unique('email');

```
⚙️ Configuration
```php
'AddUniqueConstraintOnNonEmptyColumn' => [
    'enabled'  => true,
    'severity' => 'warning', // can raise to 'error' for CI enforcement
    'check_composite' => true,      // enable composite unique detection
],
```
🧾 Example output
```bash
[warning] AddUniqueConstraintOnNonEmptyColumn
→ Adding composite unique constraint on ('email', 'tenant_id') in 'users' may fail if duplicates already exist.
```
### 🧠 Recommendation

- Always scan for duplicates before adding unique constraints.
- Use two-step rollouts for production: detect + cleanup, then constrain.
- Avoid adding uniqueness inline during schema creation on large tables.
- Disable `check_composite` if composite indexes are validated elsewhere.

---

## 🧩 FloatColumnForMoney

**Category:** Precision / Data Integrity  
**Default severity:** `warning`

---

### 🔍 What it checks
Warns when a migration defines a **float** column for values that appear to represent **money or currency** (e.g., `price`, `amount`, `balance`, `total`, etc.).

The rule now detects:

- `float()`, `double()`, or `real()` methods used on money-like columns.
- Column names containing terms such as `_price`, `_amount`, `_cost`, `_balance`, `_tax`, `_fee`, `_total`, `_revenue`, `_discount`, `_charge`, `_credit`, etc.
- Configurable detection for `double()` and `real()` usage.

---

### 💣 Why it matters
- Floating-point numbers introduce rounding errors that can silently corrupt financial totals.
- Different database engines may round differently (especially MySQL vs. PostgreSQL).
- Financial, billing, and accounting tables require exact precision — use decimal(p, s) instead.

---

### ⚠️ Triggers
```php
// 🚫 Inaccurate monetary representation
$table->float('price');
$table->double('amount');
$table->real('balance');
$table->float('tax');
$table->float('total');

```
✅ Recommended alternatives
```php
// ✅ Use fixed-point decimal type with precision and scale
$table->decimal('price', 10, 2);
$table->decimal('amount', 12, 4);

// ✅ For safety, define a custom schema macro
$table->money('price'); // wraps decimal(10, 2)

```
⚙️ Configuration
```php
'FloatColumnForMoney' => [
    'enabled'  => true,
    'severity' => 'warning', // can be 'error' for strict CI mode
    'check_double' => true,      // include double() in checks
    'check_real'   => true,      // include real() in checks
],
```
🧾 Example output
```bash
[warning] FloatColumnForMoney
→ Column 'amount' in table 'payments' uses double(); consider using decimal(10,2) or storing values in minor units (e.g. cents).

```
### 🧠 Recommendation
- Use `decimal(p, s)` (commonly `decimal(10,2)` or `decimal(12,4)`) for any money, cost, tax, or balance fields.
- Reserve `float()`, `double()`, or `real()` for scientific or approximate data (measurements, ratios, etc.).
- Avoid floating-point math in any table representing financial accuracy or audits.

---

## 🧩 SoftDeletesOnProduction

**Category:** Performance / Data Management  
**Default severity:** `warning`

---

### 🔍 What it checks
Warns when soft deletes (`->softDeletes()`) are added to **large tables**, which can impact query performance and complexity.

Soft-deleted records remain in the database and must be excluded from queries, adding extra `WHERE deleted_at IS NULL` conditions. On large tables, this can:
- Slow down queries significantly
- Create index bloat
- Complicate reporting and analytics
- Increase backup sizes

The rule checks tables listed in `large_table_names` config by default.

---

### 💣 Why it matters
- Large tables with millions of rows + soft deletes = slower queries
- Each query must filter `deleted_at IS NULL`, stressing indexes
- Soft-deleted records accumulate in production databases over time
- Analytics queries become complex with deletion logic
- Backups grow unnecessarily large

---

### ⚠️ Triggers
```php
// 🚫 Soft deletes on large tables (users, orders, invoices by default)
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email');
    $table->softDeletes();  // ⚠️ Triggers on 'users' table
});

Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id');
    $table->softDeletes();  // ⚠️ Triggers on 'orders' table
});

// ✅ Soft deletes on small tables (ignored by default)
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->softDeletes();  // OK - 'tags' is not in large_table_names
});
```

✅ Better alternatives
```php
// Option 1: Archive to separate table instead
Schema::create('users_archive', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('original_id');
    $table->json('archived_data');
    $table->timestamp('archived_at')->useCurrent();
});

// Option 2: Use hard deletes with backups
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email');
    // Rely on backups instead of soft deletes
});

// Option 3: If soft deletes required, add index on deleted_at
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email');
    $table->softDeletes();
    $table->index('deleted_at');  // ← Add this for performance
});
```

⚙️ Configuration
```php
'SoftDeletesOnProduction' => [
    'enabled'  => true,
    'severity' => 'warning', // can be 'error' for strict CI
    'check_all_tables' => false,  // false = only check large_table_names
],
```

Global settings (shared with other rules):
```php
'large_table_names' => ['users', 'orders', 'invoices'],
```

If you want to check soft deletes on ALL tables (not just large ones):
```php
'SoftDeletesOnProduction' => [
    'check_all_tables' => true,  // Check all tables for soft deletes
],
```

🧾 Example output
```bash
[warning] SoftDeletesOnProduction
→ Soft deletes on table 'users' may impact query performance. Large tables with soft deletes require careful indexing.

[Suggestion #1] SoftDeletesOnProduction:
  Option 1: Consider archiving old records to a separate table instead
  Option 2: Use hard deletes with proper backups for large tables
  Option 3: If soft deletes needed, ensure you have indexes on 'deleted_at' column
  📖 Learn more: https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/docs/rules#-softdeletesonproduction
```

### 🧠 Recommendation

- **Default:** Avoid soft deletes on large production tables (> 100k rows)
- **If necessary:** Add index on `deleted_at` column for query performance
- **Better approach:** Archive old data to separate tables or use hard deletes
- **Query optimization:** Always explicitly join with `->whereNull('deleted_at')` or use Eloquent's automatic scoping
- **Reporting:** Consider separate read-only archive tables for analytics on deleted data

---
````