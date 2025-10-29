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
        return 'Warns when one or more columns are dropped without explicit confirmation or backup.';
    }

    /**
     * @return Issue[]
     */
    public function check(Operation $operation): array
    {
        if (strtolower($operation->method) !== 'dropcolumn') {
            return [];
        }

        $issues  = [];
        $columns = [];

        $rawCode = strtolower($operation->rawCode ?? '');
        $args    = strtolower($operation->args ?? '');

        // ---------------------------------------------------------------------
        // 1️⃣ Allow developer opt-out via safe comment
        // ---------------------------------------------------------------------
        if (str_contains($rawCode, '// safe drop') || str_contains($rawCode, '/* safe-drop')) {
            return [];
        }

        // ---------------------------------------------------------------------
        // 2️⃣ Extract dropped column names (single or multiple)
        // ---------------------------------------------------------------------
        if (preg_match_all("/'([^']+)'/", $args, $m)) {
            $columns = $m[1];
        }

        // ---------------------------------------------------------------------
        // 3️⃣ Generate issues
        // ---------------------------------------------------------------------
        if (! empty($columns)) {
            // Multiple columns
            if (count($columns) > 1) {
                $colList = implode("', '", $columns);
                $issues[] = $this->warn(
                    $operation,
                    "Dropping multiple columns ('{$colList}') from table '{$operation->table}' may result in data loss."
                );
            } else {
                // Single column
                $col = $columns[0];
                $issues[] = $this->warn(
                    $operation,
                    "Dropping column '{$col}' from table '{$operation->table}' may result in data loss.",
                    $col
                );
            }
        } else {
            // Generic warning if no column detected
            $issues[] = $this->warn(
                $operation,
                "Dropping one or more columns from '{$operation->table}' may result in data loss."
            );
        }

        return $issues;
    }
}
