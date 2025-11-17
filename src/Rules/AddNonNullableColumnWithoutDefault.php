<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;

class AddNonNullableColumnWithoutDefault extends AbstractRule
{
    public function id(): string
    {
        return 'AddNonNullableColumnWithoutDefault';
    }

    public function defaultSeverity(): string
    {
        return 'warning';
    }

    public function description(): string
    {
        return 'Warns when adding or altering a NOT NULL column without a default value to a table that may contain data.';
    }

public function check(Operation $operation): array
{
    $issues = [];

    // Only consider column creation/modification methods
    $columnTypes = [
        'string',
        'integer',
        'bigInteger',
        'uuid',
        'boolean',
        'timestamp',
        'text',
        'float',
        'decimal',
    ];

    if (! in_array($operation->method, $columnTypes, true)) {
        return [];
    }

    $raw  = strtolower($operation->rawCode ?? '');
    $args = strtolower($operation->args ?? '');

    // Detect default()
    $hasDefault = str_contains($raw, 'default(') || str_contains($args, 'default(');

    // Detect column modification
    $isChange = str_contains($raw, '->change()');

    // --- Nullable detection (final version) ---
    $hasExplicitFalse = str_contains($raw, '->nullable(false)') || str_contains($args, 'nullable(false)');
    $hasExplicitTrue  = str_contains($raw, '->nullable(true)')  || str_contains($args, 'nullable(true)');
    $hasGenericNullable = str_contains($raw, '->nullable(') || str_contains($args, 'nullable');

    // Only consider it nullable if explicitly true or a generic call (no explicit false)
    $hasNullable = (!$hasExplicitFalse) && ($hasExplicitTrue || $hasGenericNullable);

    // Detect new-table creation (safe)
    $isNewTable = preg_match('/create_.*_table\.php$/', strtolower($operation->file ?? ''))
        || str_contains($raw, 'schema::create(');

    if ($isNewTable) {
        return [];
    }

    // Config controls
    $largeTables = config('migration-linter.large_table_names', []);
    $checkAll    = config('migration-linter.check_all_tables', true);
    if ($checkAll === null) {
        $checkAll = true;
    }

    $shouldCheck = $checkAll || in_array($operation->table, $largeTables, true);

    // Apply lint rule
    if ($shouldCheck && ! $hasNullable && ! $hasDefault) {
        $message = $isChange
            ? sprintf(
                "Changing column '%s' on table '%s' to NOT NULL without default may fail on existing data.",
                $operation->column ?: 'unknown',
                $operation->table
            )
            : sprintf(
                "Adding NOT NULL column '%s' on table '%s' without default (type: %s).",
                $operation->column ?: 'unknown',
                $operation->table,
                $operation->method
            );

        $suggestion = "Option 1: Add a default value:\n"
            . "  \$table->{$operation->method}('{$operation->column}')->default('...')->nullable(false);\n\n"
            . "Option 2: Make it nullable, then alter:\n"
            . "  \$table->{$operation->method}('{$operation->column}')->nullable();\n"
            . "  DB::table('{$operation->table}')->update(['{$operation->column}' => '...']);\n"
            . "  \$table->{$operation->method}('{$operation->column}')->nullable(false)->change();";

        $docsUrl = 'https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/docs/rules#-addnonnullablecolumnwithoutdefault';

        $issues[] = $this->warn(
            $operation,
            $message,
            $operation->column,
            $suggestion,
            $docsUrl
        );
    }

    return $issues;
}

}
