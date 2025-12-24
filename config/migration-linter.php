<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Linter
    |--------------------------------------------------------------------------
    |
    | You can toggle the linter globally. Typically enabled only in local,
    | staging, or CI environments to avoid runtime overhead in production.
    |
    */

    'enabled' => env('MIGRATION_LINTER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Environments
    |--------------------------------------------------------------------------
    |
    | Restrict the linter to specific environments.
    |
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
    |
    | Enable or disable individual lint rules.
    |
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
            'check_all_tables' => false, // Set to true to check all tables, not just large_table_names
        ],
        'RenamingColumnWithoutIndex' => [
            'enabled'  => true,
            'severity' => 'warning',
            'check_large_tables_only' => true, // Set to false to check all tables regardless of size
        ],


    ],

];
