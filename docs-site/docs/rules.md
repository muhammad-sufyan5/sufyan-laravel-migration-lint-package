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
- [RenamingColumnWithoutIndex](#-renamingcolumnwithoutindex)
- [ChangeColumnTypeOnLargeTable](#-changecolumntypeonlargetable)

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

## 🧩 RenamingColumnWithoutIndex

**Category:** Performance / Downtime  
**Default severity:** `warning`

---

### 🔍 What it checks
Warns when using `renameColumn()` to rename database columns, which can cause **table locks** and **downtime** on production databases, especially on large tables.

The rule detects:
- `$table->renameColumn('old_name', 'new_name')` operations
- By default, only checks tables listed in `large_table_names` config
- Can be configured to check all tables regardless of size

---

### 💣 Why it matters
- **MySQL/MariaDB:** `renameColumn()` uses `ALTER TABLE` which locks the entire table during execution
- **Large tables:** Renaming a column on a table with millions of rows can take minutes or hours
- **Production impact:** All queries to that table are blocked during the rename operation
- **Deployment risk:** Can cause application downtime and timeouts during migrations
- **No rollback:** If rename fails mid-way, you may have an inconsistent schema state

---

### ⚠️ Triggers
```php
// 🚫 Direct column rename on large table
Schema::table('users', function (Blueprint $table) {
    $table->renameColumn('email_address', 'email');
});
```

✅ Safe alternatives
```php
// ✅ Zero-downtime approach (3-phase migration)

// Migration 1: Add new column
Schema::table('users', function (Blueprint $table) {
    $table->string('email')->nullable()->after('email_address');
});

// Migration 2: Migrate data (after deployment, in batches)
DB::table('users')
    ->whereNull('email')
    ->chunkById(1000, function ($records) {
        foreach ($records as $record) {
            DB::table('users')
                ->where('id', $record->id)
                ->update(['email' => $record->email_address]);
        }
    });

// Migration 3: Drop old column (after code updated to use new column)
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('email_address'); // safe drop
});

// ✅ Or bypass check if verified safe (small table, maintenance window)
Schema::table('users', function (Blueprint $table) {
    $table->renameColumn('old', 'new'); // safe rename
});
```

⚙️ Configuration
```php
'RenamingColumnWithoutIndex' => [
    'enabled'  => true,
    'severity' => 'warning', // or 'error' to block in CI
    'check_large_tables_only' => true, // false = check all tables
],
```

Global settings (shared with other rules):
```php
'large_table_names' => ['users', 'orders', 'invoices'],
```

If you want to check ALL tables for column renaming (not just large ones):
```php
'RenamingColumnWithoutIndex' => [
    'check_large_tables_only' => false,  // Check all tables
],
```

🧾 Example output
```bash
[warning] RenamingColumnWithoutIndex
→ Renaming column 'email_address' to 'email' on table 'users' can cause table locks and downtime, especially on large tables.

[Suggestion #1] RenamingColumnWithoutIndex:
  For zero-downtime column renaming, use this phased approach:
  
  Migration 1 - Add new column:
    Schema::table('users', function (Blueprint $table) {
        $table->string('email')->nullable()->after('email_address');
    });
  
  Migration 2 - Migrate data (after deployment):
    DB::table('users')
        ->whereNull('email')
        ->chunkById(1000, function ($records) {
            foreach ($records as $record) {
                DB::table('users')
                    ->where('id', $record->id)
                    ->update(['email' => $record->email_address]);
            }
        });
  
  Migration 3 - Drop old column (after code updated):
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('email_address'); // safe drop
    });
  
  Alternative: Add '// safe rename' comment if you've verified the table is small or unused.
  
  📖 Learn more: https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/docs/rules#-renamingcolumnwithoutindex
```

### 🧠 Recommendation

- **Default:** Avoid direct `renameColumn()` on production tables with > 10k rows
- **Best practice:** Use 3-phase migration approach (add → migrate → drop)
- **Data migration:** Always use `chunkById()` to avoid memory issues on large datasets
- **Deployment:** Allow time between phases for code deployment and verification
- **Monitoring:** Monitor query performance during data migration phase
- **Safe bypass:** Use `// safe rename` comment only after verifying:
  - Table is small (< 10k rows)
  - Rename happens during maintenance window
  - You have database backups

---

## 🔄 ChangeColumnTypeOnLargeTable

**Category:** Performance / Downtime Risk  
**Default severity:** `error`

---

### 🔍 What it checks
Detects when you're changing a column's data type using `->change()` on large tables. This operation requires MySQL/PostgreSQL to rebuild the entire table, which can cause:
- Extended table locks (minutes to hours)
- Application downtime
- Database connection exhaustion
- Failed deployments on production

The rule detects 25+ column type methods including:
- String types: `string()`, `char()`, `text()`, `longText()`, etc.
- Numeric types: `integer()`, `bigInteger()`, `decimal()`, `float()`, etc.
- Date/time types: `datetime()`, `timestamp()`, `date()`, etc.
- Other types: `boolean()`, `enum()`, `json()`, `uuid()`, etc.

---

### 💣 Why it matters
- **Table Locks:** ALTER TABLE locks the entire table during type changes
- **Long Duration:** On tables with millions of rows, this can take hours
- **Downtime:** Users get "table is locked" errors during the migration
- **Memory Issues:** Large tables may cause out-of-memory errors
- **Failed Deploys:** Migrations may timeout in production

**Real-world impact:**
- 1M row table: 5-30 minutes of downtime
- 10M row table: 30 minutes - 2 hours of downtime
- 100M+ row table: Multiple hours, may require special tools

---

### ⚠️ Triggers

```php
// ❌ Dangerous: Locks entire table
Schema::table('users', function (Blueprint $table) {
    $table->string('email', 255)->change();  // String length change
    $table->bigInteger('amount')->change();  // Type change
    $table->decimal('price', 10, 2)->change(); // Precision change
});

// ❌ Also detected
$table->text('description')->change();
$table->datetime('created_at')->change();
$table->boolean('is_active')->change();
$table->enum('status', ['active', 'inactive'])->change();
```

### ✅ Safe Alternatives

**Option 1: Zero-Downtime Multi-Step Migration (Recommended)**

```php
// Migration 1: Add new column with desired type
Schema::table('users', function (Blueprint $table) {
    $table->string('email_new', 255)->nullable()->after('email');
});

// Migration 2: Backfill data in batches (run after deploy)
DB::table('users')
    ->whereNull('email_new')
    ->chunkById(1000, function ($records) {
        foreach ($records as $record) {
            DB::table('users')
                ->where('id', $record->id)
                ->update(['email_new' => $record->email]);
        }
    });

// Migration 3: Update application code to use email_new
// (Deploy application changes)

// Migration 4: Drop old column
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('email'); // safe drop
    $table->renameColumn('email_new', 'email'); // safe rename
});
```

**Option 2: Maintenance Window Approach**

```php
// Schedule during low-traffic period (e.g., 2 AM)
// Put application in maintenance mode
Artisan::call('down');

// Run migration with increased timeout
DB::statement('SET SESSION max_execution_time = 7200;');

Schema::table('users', function (Blueprint $table) {
    $table->string('email', 255)->change(); // maintenance window
});

Artisan::call('up');
```

**Option 3: Use pt-online-schema-change (Percona Toolkit)**

```bash
# For MySQL databases - performs changes without locking
pt-online-schema-change \
  --alter "MODIFY email VARCHAR(255)" \
  --execute \
  D=your_database,t=users
```

---

### ⚙️ Configuration

```php
'ChangeColumnTypeOnLargeTable' => [
    'enabled'  => true,
    'severity' => 'error', // High severity - can cause significant downtime
    'check_large_tables_only' => true, // false = check all tables
],
```

Global settings (shared with other rules):
```php
'large_table_names' => ['users', 'orders', 'invoices'],
```

To check ALL tables (not just large ones):
```php
'ChangeColumnTypeOnLargeTable' => [
    'check_large_tables_only' => false,  // Check all tables
],
```

---

### 🧾 Example Output

```bash
[error] ChangeColumnTypeOnLargeTable
→ Changing column type of 'email' to string(255) on table 'users' requires ALTER TABLE, which locks the entire table and can cause downtime on large datasets.

[Suggestion #1] ChangeColumnTypeOnLargeTable:
  Changing column types on large tables can take minutes or hours. Consider these approaches:
  
  Option 1 - Zero-downtime approach (Recommended):
    1. Add new column with desired type:
       Schema::table('users', function (Blueprint $table) {
           $table->string('email_new')->nullable();
       });
    
    2. Backfill data in batches:
       DB::table('users')
           ->whereNull('email_new')
           ->chunkById(1000, function ($records) {
               foreach ($records as $record) {
                   DB::table('users')
                       ->where('id', $record->id)
                       ->update(['email_new' => $record->email]);
               }
           });
    
    3. Update application code to use new column
    
    4. Drop old column (after verification):
       Schema::table('users', function (Blueprint $table) {
           $table->dropColumn('email'); // safe drop
       });
  
  Option 2 - Maintenance window approach:
    • Schedule during low-traffic period
    • Put application in maintenance mode
    • Run migration with timeout buffer
    • Monitor query execution time
  
  Option 3 - Use raw SQL with pt-online-schema-change:
    • Use Percona Toolkit for MySQL
    • Performs changes without locking table
    • Example: pt-online-schema-change --alter="MODIFY email ..." D=users
  
  To bypass this warning (if table is small or during maintenance):
    Add '// safe change' or '// maintenance window' comment to the line.
  
  📖 Learn more: https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/docs/rules#-changecolumntypeonlargetable
```

---

### 🧠 Recommendation

- **Default:** Avoid direct type changes on production tables with > 100k rows
- **Best practice:** Use zero-downtime multi-step approach (add → backfill → switch → drop)
- **Batch processing:** Always use `chunkById()` for data migration to avoid memory issues
- **Testing:** Test the full migration process on a staging database with production-sized data
- **Monitoring:** Monitor query execution time and table lock duration
- **Maintenance window:** If using Option 2, communicate downtime window to users
- **Tools:** Consider pt-online-schema-change for MySQL or pg_repack for PostgreSQL
- **Safe bypass:** Use `// safe change` or `// maintenance window` comments only after:
  - Verifying table is small (< 100k rows)
  - Scheduling during maintenance window
  - Testing on staging with real data volume
  - Having rollback plan and database backups

**Detected Column Type Methods:**
- String: `string`, `char`, `varchar`, `text`, `mediumText`, `longText`
- Numeric: `integer`, `tinyInteger`, `smallInteger`, `mediumInteger`, `bigInteger`, `unsignedInteger`, `float`, `double`, `decimal`, `unsignedDecimal`
- Date/Time: `date`, `datetime`, `datetimeTz`, `time`, `timeTz`, `timestamp`, `timestampTz`, `year`
- Other: `boolean`, `enum`, `json`, `jsonb`, `binary`, `uuid`, `ipAddress`, `macAddress`

---
````