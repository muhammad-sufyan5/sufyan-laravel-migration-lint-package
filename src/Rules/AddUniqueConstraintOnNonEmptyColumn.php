<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;

class AddUniqueConstraintOnNonEmptyColumn extends AbstractRule
{
    public function id(): string
    {
        return 'AddUniqueConstraintOnNonEmptyColumn';
    }

    public function defaultSeverity(): string
    {
        return 'warning';
    }

    public function description(): string
    {
        return 'Warns when a unique constraint is added to existing columns which may contain duplicate data.';
    }

    /**
     * @return \Sufyan\MigrationLinter\Support\Issue[]
     */
    public function check(Operation $operation): array
    {
        $issues = [];

        $config = config('migration-linter.rules.AddUniqueConstraintOnNonEmptyColumn', []);
        $checkComposite = $config['check_composite'] ?? true;

        $method = strtolower($operation->method);
        $raw    = strtolower($operation->rawCode ?? '');
        $args   = strtolower($operation->args ?? '');

        // ---------------------------------------------------------------------
        // 1️⃣ explicit $table->unique('email') or $table->unique(['email','tenant_id'])
        // ---------------------------------------------------------------------
        if ($method === 'unique') {
            $columns = [];
            if (preg_match_all("/'([^']+)'/", $operation->args ?? '', $m)) {
                $columns = $m[1];
            }

            // detect composite key
            if ($checkComposite && count($columns) > 1) {
                $colList = implode("', '", $columns);
                $issues[] = $this->warn(
                    $operation,
                    "Adding composite unique constraint on ('{$colList}') in '{$operation->table}' may fail if duplicates already exist. " .
                    "Consider cleaning data before applying this migration."
                );
                return $issues;
            }

            // single-column case
            if (count($columns) === 1) {
                $issues[] = $this->warn(
                    $operation,
                    "Adding unique constraint to '{$columns[0]}' in '{$operation->table}' may fail if duplicates already exist. " .
                    "Consider deduplicating existing records.",
                    $columns[0]
                );
                return $issues;
            }

            // generic fallback
            $issues[] = $this->warn(
                $operation,
                "Adding a unique constraint in '{$operation->table}' may fail if duplicates already exist."
            );
            return $issues;
        }

        // ---------------------------------------------------------------------
        // 2️⃣ inline ->unique() on a column definition (string, integer, etc.)
        // ---------------------------------------------------------------------
        $columnTypes = ['string', 'integer', 'biginteger', 'uuid', 'char', 'text'];
        if (in_array($method, $columnTypes, true)) {
            $hasInlineUnique =
                str_contains($raw, '->unique(') ||
                str_contains($args, 'unique');

            if ($hasInlineUnique) {
                $col = null;
                if (preg_match("/'([^']+)'/", $operation->args ?? '', $m)) {
                    $col = $m[1];
                }

                $message = $col
                    ? "Adding unique constraint inline on '{$col}' in '{$operation->table}' may fail on existing data."
                    : "Adding inline unique constraint in '{$operation->table}' may fail on existing data.";

                // detect change() call
                if (str_contains($raw, '->change()')) {
                    $message .= ' This appears to modify an existing column; backfill or deduplicate before applying.';
                }

                $issues[] = $this->warn($operation, $message, $col);
            }
        }

        return $issues;
    }
}
