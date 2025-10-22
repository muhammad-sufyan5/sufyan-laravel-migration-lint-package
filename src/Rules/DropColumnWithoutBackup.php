<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

class DropColumnWithoutBackup
{
    public static string $id = 'DropColumnWithoutBackup';

    public function description(): string
    {
        return 'Warns when columns are dropped without confirmation or backup.';
    }

    /**
     * @param Operation $operation
     * @return array<Issue>
     */
    public function check(Operation $operation): array
    {
        $issues = [];

        // Only handle dropColumn operations
        if ($operation->method !== 'dropColumn') {
            return $issues;
        }

        // Try to extract the column(s) being dropped
        $columns = [];

        // If args look like "['email']" or "'email'"
        if (preg_match_all("/'([^']+)'/", $operation->args, $matches)) {
            $columns = $matches[1];
        }

        foreach ($columns as $column) {
            $issues[] = new Issue(
                ruleId: self::$id,
                file: $operation->file,
                message: "Dropping column '{$column}' from table '{$operation->table}' may result in data loss.",
                severity: 'warning',
                snippet: $column
            );
        }

        // If no specific column detected, still warn once
        if (empty($columns)) {
            $issues[] = new Issue(
                ruleId: self::$id,
                file: $operation->file,
                message: "Dropping one or more columns from '{$operation->table}' may result in data loss.",
                severity: 'warning'
            );
        }

        return $issues;
    }
}
