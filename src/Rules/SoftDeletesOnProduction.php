<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;

class SoftDeletesOnProduction extends AbstractRule
{
    public function id(): string
    {
        return 'SoftDeletesOnProduction';
    }

    public function defaultSeverity(): string
    {
        return 'warning';
    }

    public function description(): string
    {
        return 'Warns when soft deletes are added to large tables, which can impact performance and query complexity.';
    }

    public function check(Operation $operation): array
    {
        $issues = [];

        // Normalize input
        $method = strtolower($operation->method ?? '');
        $raw    = strtolower(trim($operation->rawCode ?? ''));

        // Check if this is a softDeletes method
        if ($method !== 'softdeletes') {
            return $issues;
        }

        // Get configuration
        $config = config('migration-linter.rules.SoftDeletesOnProduction', []);
        $checkAllTables = $config['check_all_tables'] ?? false;
        $largeTableNames = config('migration-linter.large_table_names', ['users', 'orders', 'invoices']);

        // Determine if we should check this table
        $shouldWarn = $checkAllTables || in_array($operation->table, $largeTableNames, true);

        if ($shouldWarn) {
            $suggestion = "Soft deletes on large tables can impact performance:\n"
                . "  Option 1: Consider archiving old records to a separate table instead\n"
                . "  Option 2: Use hard deletes with proper backups for large tables\n"
                . "  Option 3: If soft deletes needed, ensure you have indexes on 'deleted_at' column";

            $issues[] = $this->warn(
                $operation,
                sprintf(
                    "Soft deletes on table '%s' may impact query performance. Large tables with soft deletes require careful indexing.",
                    $operation->table
                ),
                'deleted_at',
                $suggestion,
                'https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/docs/rules#-softdeletesonproduction'
            );
        }

        return $issues;
    }
}
