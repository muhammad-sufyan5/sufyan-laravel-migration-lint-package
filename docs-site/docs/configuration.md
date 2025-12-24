---
id: configuration
title: 🧱 Configuration
sidebar_position: 4
---

The **Laravel Migration Linter** ships with a configurable file that defines which rules are active and how severe their findings should be.  
You can customize this file to match your team's migration safety standards.

---

## ⚙️ Publishing the Config

If you haven’t already, publish the configuration file to your app:

```bash
php artisan vendor:publish --tag="migration-linter-config"
```
This creates:
```arduino
config/migration-linter.php
```
---

### 📄 Default Config File

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Linter
    |--------------------------------------------------------------------------
    */
    'enabled' => env('MIGRATION_LINTER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Environments
    |--------------------------------------------------------------------------
    */
    'environments' => ['local', 'staging', 'testing'],

    /*
    |--------------------------------------------------------------------------
    | Severity Threshold
    |--------------------------------------------------------------------------
    |
    | Minimum severity that should trigger a non-zero exit code in CI.
    | Options: info | warning | error
    |
    */
    'severity_threshold' => 'warning',

    /*
    |--------------------------------------------------------------------------
    | Known Large Tables
    |--------------------------------------------------------------------------
    |
    | Large tables are more likely to cause locks or downtime.
    |
    */
    'large_table_names' => ['users', 'orders', 'invoices'],

    /*
    |--------------------------------------------------------------------------
    | Check All Tables
    |--------------------------------------------------------------------------
    |
    | If true, the linter will check every table, not just those listed in
    | "large_table_names". If false, only large tables will be checked.
    |
    */
    'check_all_tables' => false,

    /*
    |--------------------------------------------------------------------------
    | Excluded Paths
    |--------------------------------------------------------------------------
    |
    | Paths inside `database/migrations` that should be ignored.
    |
    */
    'exclude_paths' => [],

    /*
    |--------------------------------------------------------------------------
    | Rule Toggles
    |--------------------------------------------------------------------------
    */
    'rules' => [
        'AddNonNullableColumnWithoutDefault' => [
            'enabled' => true,
            'severity' => 'warning',
        ],
        'MissingIndexOnForeignKey' => [
            'enabled' => true,
            'severity' => 'warning',
            'check_foreign_id_without_constrained' => true,
            'check_morphs_without_index' => true,
            'check_composite_foreign' => true,
        ],
        'DropColumnWithoutBackup' => [
            'enabled'  => true,
            'severity' => 'warning',
            'allow_safe_comment' => true,
        ],
        'AddUniqueConstraintOnNonEmptyColumn' => [
            'enabled'  => true,
            'severity' => 'warning',
            'check_composite' => true,
        ],
        'FloatColumnForMoney' => [
            'enabled'  => true,
            'severity' => 'warning',
            'check_double' => true,
            'check_real'   => true,
        ],
        'SoftDeletesOnProduction' => [
            'enabled'  => true,
            'severity' => 'warning',
            'check_all_tables' => false, // Check only large_table_names
        ],
        'RenamingColumnWithoutIndex' => [
            'enabled'  => true,
            'severity' => 'warning',
            'check_large_tables_only' => true, // Check only large tables
        ],
        'ChangeColumnTypeOnLargeTable' => [
            'enabled'  => true,
            'severity' => 'error', // High severity - can cause significant downtime
            'check_large_tables_only' => true, // Check only large tables
        ],
    ],
];
```
---

### 🧩 Severity Levels

| Level     | Meaning                              | Exit Code | Recommended Usage                            |
| --------- | ------------------------------------ | --------- | -------------------------------------------- |
| `info`    | Advisory only; does not affect CI    | 0         | Local hints or code-style warnings           |
| `warning` | Possible performance or safety issue | 0         | Default level for most rules                 |
| `error`   | Migration-breaking or data-loss risk | 1         | Use in CI/CD pipelines to block risky merges |

---

### 🧠 Notes

- You can disable a rule entirely by setting `'enabled' => false`.
- To treat warnings as failures in CI, change `severity_threshold` to `'error'`.
- Custom rules defined in `App\MigrationRules` or any namespaced class are automatically discovered.
- Each rule now includes **actionable suggestions** in its output.
- Large table configuration (`large_table_names`) is shared across multiple rules (SoftDeletesOnProduction, RenamingColumnWithoutIndex).
- Use `'check_all_tables' => true` to check all tables regardless of the `large_table_names` list.
- Safe comment bypass: Add `// safe drop`, `// safe rename`, etc., to bypass specific warnings.
- See [📏 Available Rules](./rules.md) for detailed documentation on each rule.
- See [🧠 Writing Custom Rules](./writing-custom-rules.md) for creating your own rules.

---

### 🔧 Rule-Specific Options

#### MissingIndexOnForeignKey
```php
'check_foreign_id_without_constrained' => true,  // Check foreignId() without ->constrained()
'check_morphs_without_index' => true,            // Check morphs() without ->index()
'check_composite_foreign' => true,               // Check composite foreign keys
```

#### DropColumnWithoutBackup
```php
'allow_safe_comment' => true,  // Allow '// safe drop' to bypass warning
```

#### AddUniqueConstraintOnNonEmptyColumn
```php
'check_composite' => true,  // Check composite unique constraints
```

#### FloatColumnForMoney
```php
'check_double' => true,  // Check double() columns
'check_real' => true,    // Check real() columns
```

#### SoftDeletesOnProduction
```php
'check_all_tables' => false,  // Only check large_table_names (default)
```

#### RenamingColumnWithoutIndex
```php
'check_large_tables_only' => true,  // Only check large_table_names (default)
```

#### ChangeColumnTypeOnLargeTable
```php
'check_large_tables_only' => true,  // Only check large_table_names (default)
'severity' => 'error',              // High severity due to potential for significant downtime
```


✅ Pro Tip: Commit your config/migration-linter.php file to version control so your whole team shares the same linting standards.

---