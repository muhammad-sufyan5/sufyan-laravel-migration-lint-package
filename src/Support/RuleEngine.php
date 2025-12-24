<?php

namespace Sufyan\MigrationLinter\Support;

use Illuminate\Support\Facades\App;
use Sufyan\MigrationLinter\Contracts\SeverityResolverInterface;
use Sufyan\MigrationLinter\Rules\FloatColumnForMoney;
use Sufyan\MigrationLinter\Rules\DropColumnWithoutBackup;
use Sufyan\MigrationLinter\Rules\MissingIndexOnForeignKey;
use Sufyan\MigrationLinter\Rules\SoftDeletesOnProduction;
use Sufyan\MigrationLinter\Rules\RenamingColumnWithoutIndex;
use Sufyan\MigrationLinter\Rules\ChangeColumnTypeOnLargeTable;
use Sufyan\MigrationLinter\Rules\AddNonNullableColumnWithoutDefault;
use Sufyan\MigrationLinter\Rules\AddUniqueConstraintOnNonEmptyColumn;

class RuleEngine
{
    protected array $rules = [];

    /**
     * Optional SeverityResolverInterface dependency (for DI).
     */
    protected ?SeverityResolverInterface $severityResolver = null;

    /**
     * Constructor - can optionally inject SeverityResolverInterface.
     *
     * @param SeverityResolverInterface|null $severityResolver
     */
    public function __construct(?SeverityResolverInterface $severityResolver = null)
    {
        $this->severityResolver = $severityResolver;
        $this->loadRules();
    }

    /**
     * Load enabled rules from config.
     */
    protected function loadRules(): void
    {
        // Built-in rules
        $map = [
            'AddNonNullableColumnWithoutDefault' => AddNonNullableColumnWithoutDefault::class,
            'MissingIndexOnForeignKey' => MissingIndexOnForeignKey::class,
            'DropColumnWithoutBackup' => DropColumnWithoutBackup::class,
            'AddUniqueConstraintOnNonEmptyColumn' => AddUniqueConstraintOnNonEmptyColumn::class,
            'FloatColumnForMoney' => FloatColumnForMoney::class,
            'SoftDeletesOnProduction' => SoftDeletesOnProduction::class,
            'RenamingColumnWithoutIndex' => RenamingColumnWithoutIndex::class,
            'ChangeColumnTypeOnLargeTable' => ChangeColumnTypeOnLargeTable::class,
        ];

        // Load config-defined rules (allows app or custom packages)
        $configured = config('migration-linter.rules', []);

        foreach ($configured as $key => $settings) {
            if (!is_array($settings) || empty($settings['enabled'])) {
                continue;
            }

            // Determine class: use map, FQCN, or default package namespace
            $class = $map[$key]
                ?? (class_exists($key) ? $key : "App\\MigrationRules\\{$key}");

            if (!class_exists($class)) {
                continue; // skip invalid entries
            }

            /** @var \Sufyan\MigrationLinter\Rules\AbstractRule $rule */
            $rule = app($class);

            // ✅ Phase 5: Inject SeverityResolverInterface if available
            if ($this->severityResolver) {
                $rule->setSeverityResolver($this->severityResolver);
            }

            if (isset($settings['severity'])) {
                $rule->customSeverity = $settings['severity'];
            }

            $this->rules[] = $rule;
        }
    }



    /**
     * Run all enabled rules against parsed operations.
     *
     * @param array<int, Operation> $operations
     * @return Issue[]
     */
    public function run(array $operations): array
    {
        $issues = [];

        foreach ($operations as $opData) {
            // Convert array from parser to an Operation object
            $operation = new \Sufyan\MigrationLinter\Support\Operation(
                table: $opData['table'] ?? '',
                method: $opData['method'] ?? '',
                args: $opData['args'] ?? '',
                file: $opData['file'] ?? '',
                path: $opData['path'] ?? '',
                line: $opData['line'] ?? null,
                rawCode: $opData['rawCode'] ?? null,
                column: $opData['column'] ?? null,
            );

            foreach ($this->rules as $rule) {
                /** @var \Sufyan\MigrationLinter\Rules\AbstractRule $rule */
                $issues = array_merge($issues, $rule->check($operation));
            }
        }

        return $issues;
    }
}
