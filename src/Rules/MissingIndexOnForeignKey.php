<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;

class MissingIndexOnForeignKey extends AbstractRule
{
    public function id(): string
    {
        return 'MissingIndexOnForeignKey';
    }

    public function defaultSeverity(): string
    {
        return 'warning';
    }

    public function description(): string
    {
        return 'Warns when a foreign key or polymorphic column is added without an index or foreign constraint.';
    }

    public function check(Operation $operation): array
    {
        $issues = [];

        $config = config('migration-linter.rules.MissingIndexOnForeignKey', []);

        $checkForeignId = $config['check_foreign_id_without_constrained'] ?? true;
        $checkMorphs    = $config['check_morphs_without_index'] ?? true;
        $checkComposite = $config['check_composite_foreign'] ?? true;

        // Normalize input
        $method = strtolower($operation->method ?? '');
        $raw    = strtolower(trim($operation->rawCode ?? ''));
        $args   = strtolower($operation->args ?? '');

        // ---------------------------------------------------------------------
        // 1️⃣ foreignId / foreignIdFor without ->constrained()
        // ---------------------------------------------------------------------
        if (
            $checkForeignId &&
            in_array($method, ['foreignid', 'foreignidfor'], true)
        ) {
            if (! str_contains($raw, '->constrained(')) {
                $suggestion = "Add ->constrained() to your foreign key definition:\n"
                    . "  \$table->foreignId('{$operation->column}')->constrained();";

                $issues[] = $this->warn(
                    $operation,
                    sprintf(
                        "Column '%s' on table '%s' uses %s() but has no ->constrained(); constraint or index may be missing.",
                        $operation->column ?? 'unknown',
                        $operation->table,
                        $method
                    ),
                    $operation->column,
                    $suggestion,
                    'https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/docs/rules#-missingindexonforeignkey'
                );
            }
        }

        // ---------------------------------------------------------------------
        // 2️⃣ morphs / nullableMorphs without ->index()
        // ---------------------------------------------------------------------
        if (
            $checkMorphs &&
            in_array($method, ['morphs', 'nullablemorphs'], true)
        ) {
            if (! str_contains($raw, '->index(')) {
                $suggestion = "Add ->index() to your polymorphic relation for better query performance:\n"
                    . "  \$table->morphs('{$operation->column}')->index();";

                $issues[] = $this->warn(
                    $operation,
                    sprintf(
                        "Polymorphic relation '%s' on table '%s' has no index; consider adding ->index() for faster lookups.",
                        $operation->column ?? 'unknown',
                        $operation->table
                    ),
                    $operation->column,
                    $suggestion,
                    'https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/docs/rules#-missingindexonforeignkey'
                );
            }
        }

        // ---------------------------------------------------------------------
        // 3️⃣ Composite foreign([...]) without index([...])
        // ---------------------------------------------------------------------
        if ($checkComposite && $method === 'foreign' && str_contains($args, '[')) {
            if (! str_contains($raw, '->index(')) {
                $issues[] = $this->warn(
                    $operation,
                    sprintf(
                        "Composite foreign key on table '%s' may be missing a matching index on the same columns.",
                        $operation->table
                    )
                );
            }
        }


        return $issues;
    }
}
