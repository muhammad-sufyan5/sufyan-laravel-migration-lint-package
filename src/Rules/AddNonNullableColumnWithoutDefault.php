<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

class AddNonNullableColumnWithoutDefault extends AbstractRule
{
    public function id(): string
    {
        return 'AddNonNullableColumnWithoutDefault';
    }

    public function description(): string
    {
        return 'Warns when adding a NOT NULL column without a default value to a large table.';
    }

    public function check(Operation $operation): array
    {
        $issues = [];

        // Only consider "column creation" operations
        $addColumnTypes = ['string', 'integer', 'bigInteger', 'uuid', 'boolean', 'timestamp', 'text', 'float', 'decimal'];

        if (! in_array($operation->method, $addColumnTypes, true)) {
            return [];
        }

        $args = strtolower($operation->args);
        $hasNullable = str_contains($args, 'nullable');
        $hasDefault = str_contains($args, 'default(');

        // Configuration options
        $largeTables = config('migration-linter.large_table_names', []);
        $checkAll = config('migration-linter.check_all_tables', true);

        // Should we lint this table?
        $shouldCheck = $checkAll || in_array($operation->table, $largeTables, true);

        // Apply the rule
        if ($shouldCheck && ! $hasNullable && ! $hasDefault) {
            $issues[] = $this->warn($operation, sprintf(
                "Adding NOT NULL column '%s' on table '%s' without default (type: %s).",
                $operation->column ?: 'unknown',
                $operation->table,
                $operation->method
            ));
        }

        return $issues;
    }
}
