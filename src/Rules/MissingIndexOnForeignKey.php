<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

class MissingIndexOnForeignKey extends AbstractRule
{
    public function id(): string
    {
        return 'MissingIndexOnForeignKey';
    }

    public function description(): string
    {
        return 'Warns when a foreign key column is added without an index or foreign constraint.';
    }

    public function check(Operation $operation): array
    {
        $issues = [];

        // Skip if not a column creation operation
        $addColumnMethods = [
            'unsignedBigInteger',
            'unsignedInteger',
            'bigInteger',
            'integer',
        ];

        if (! in_array($operation->method, $addColumnMethods, true)) {
            return [];
        }

        // Check for likely foreign key column (ends with _id)
        if (! $operation->column || ! str_ends_with($operation->column, '_id')) {
            return [];
        }

        // Detect if this migration already has an index or foreign constraint
        // by scanning the migration body (rawCode or args)
        $hasIndexOrForeign = false;

        if ($operation->rawCode) {
            $code = strtolower($operation->rawCode);
            if (str_contains($code, '->index(') || str_contains($code, '->foreign(')) {
                $hasIndexOrForeign = true;
            }
        }

        if (! $hasIndexOrForeign) {
            $issues[] = $this->warn($operation, sprintf(
                "Foreign key-like column '%s' on table '%s' may be missing an index or constraint.",
                $operation->column,
                $operation->table
            ));
        }

        return $issues;
    }
}
