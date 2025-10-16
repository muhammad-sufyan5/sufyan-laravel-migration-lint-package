<?php

namespace Sufyan\MigrationLinter\Support;

use Sufyan\MigrationLinter\Rules\AbstractRule;
use Illuminate\Support\Facades\App;

class RuleEngine
{
    protected array $rules = [];

    public function __construct()
    {
        $this->loadRules();
    }

    /**
     * Load enabled rules from config.
     */
    protected function loadRules(): void
    {
        $map = [
            'AddNonNullableColumnWithoutDefault' => \Sufyan\MigrationLinter\Rules\AddNonNullableColumnWithoutDefault::class,
            // future rules go here...
        ];

        foreach ($map as $key => $class) {
            if (config("migration-linter.rules.$key", false)) {
                $this->rules[] = App::make($class);
            }
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
