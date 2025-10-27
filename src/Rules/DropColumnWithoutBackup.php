<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

class DropColumnWithoutBackup extends AbstractRule
{
    public function id(): string
    {
        return 'DropColumnWithoutBackup';
    }

    public function defaultSeverity(): string
    {
        return 'warning'; // can be overridden via config
    }

    public function description(): string
    {
        return 'Warns when columns are dropped without confirmation or backup.';
    }

    /**
     * @return Issue[]
     */
    public function check(Operation $operation): array
    {
        if ($operation->method !== 'dropColumn') {
            return [];
        }

        $issues  = [];
        $columns = [];

        // Extract column names from args like "['email', 'nickname']" or "'email'"
        if (preg_match_all("/'([^']+)'/", $operation->args ?? '', $m)) {
            $columns = $m[1];
        }

        // Column-specific warnings
        foreach ($columns as $col) {
            $issues[] = $this->warn(
                $operation,
                "Dropping column '{$col}' from table '{$operation->table}' may result in data loss.",
                $col
            );
        }

        // Generic warning if no specific column detected
        if (empty($columns)) {
            $issues[] = $this->warn(
                $operation,
                "Dropping one or more columns from '{$operation->table}' may result in data loss."
            );
        }

        return $issues;
    }
}
