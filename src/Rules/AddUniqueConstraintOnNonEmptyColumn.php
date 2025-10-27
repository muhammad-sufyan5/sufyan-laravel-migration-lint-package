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
        return 'warning'; // overridable via config
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

        // --- Case 1: explicit $table->unique('email') or $table->unique(['email','tenant_id'])
        if ($operation->method === 'unique') {
            $columns = [];

            if (preg_match_all("/'([^']+)'/", $operation->args ?? '', $m)) {
                $columns = $m[1];
            }

            // Emit one issue per column (simple + matches your previous behavior)
            foreach ($columns as $col) {
                $issues[] = $this->warn(
                    $operation,
                    "Adding unique constraint to '{$col}' may fail if duplicates already exist in '{$operation->table}'.",
                    $col
                );
            }

            // Handle generic call with no detectable column name
            if (empty($columns)) {
                $issues[] = $this->warn(
                    $operation,
                    "Adding a unique constraint may fail if duplicates already exist in '{$operation->table}'."
                );
            }

            return $issues;
        }

        // --- Case 2: chained inline ->unique() on a column definition, e.g. $table->string('email')->unique()
        $columnTypes = ['string', 'integer', 'bigInteger', 'uuid', 'char', 'text'];
        if (in_array($operation->method, $columnTypes, true)) {
            $hasInlineUnique =
                ($operation->rawCode && str_contains($operation->rawCode, '->unique('))
                || ($operation->args && str_contains($operation->args, 'unique'));

            if ($hasInlineUnique) {
                $col = null;
                if (preg_match("/'([^']+)'/", $operation->args ?? '', $m)) {
                    $col = $m[1];
                }

                $issues[] = $this->warn(
                    $operation,
                    $col
                        ? "Adding unique constraint inline on '{$col}' in '{$operation->table}' may fail on existing data."
                        : "Adding inline unique constraint in '{$operation->table}' may fail on existing data.",
                    $col
                );
            }
        }

        return $issues;
    }
}
