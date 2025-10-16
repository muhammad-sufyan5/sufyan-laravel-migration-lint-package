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
        'AddNonNullableColumnWithoutDefault' => true,
        'MissingIndexOnForeignKey' => true,
        'TypeChangeRisky' => true,
        'DropColumnWithoutBackfill' => true,
    ],
];
