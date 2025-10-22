<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

class AddUniqueConstraintOnNonEmptyColumn
{
    public static string $id = 'AddUniqueConstraintOnNonEmptyColumn';

    public function description(): string
    {
        return 'Warns when a unique constraint is added to existing columns which may contain duplicate data.';
    }

    /**
     * @param Operation $operation
     * @return array<Issue>
     */
    public function check(Operation $operation): array
    {
        $issues = [];

        // Detect patterns like $table->unique('email')
        if ($operation->method === 'unique') {
            $columns = [];

            if (preg_match_all("/'([^']+)'/", $operation->args, $matches)) {
                $columns = $matches[1];
            }

            foreach ($columns as $column) {
                $issues[] = new Issue(
                    ruleId: self::$id,
                    file: $operation->file,
                    message: "Adding unique constraint to '{$column}' may fail if duplicates already exist in '{$operation->table}'.",
                    severity: 'warning',
                    snippet: $column
                );
            }

            return $issues;
        }

        // Detect chained unique() calls like $table->string('email')->unique();
        if (in_array($operation->method, ['string', 'integer', 'bigInteger', 'uuid', 'char', 'text'])
            && str_contains($operation->args, 'unique')
        ) {
            $issues[] = new Issue(
                ruleId: self::$id,
                file: $operation->file,
                message: "Adding unique constraint inline on '{$operation->method}' field in '{$operation->table}' may fail on existing data.",
                severity: 'warning'
            );
        }

        return $issues;
    }
}
