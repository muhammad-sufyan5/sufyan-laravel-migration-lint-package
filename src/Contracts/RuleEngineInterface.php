<?php

namespace Sufyan\MigrationLinter\Contracts;

/**
 * Contract for executing linting rules.
 *
 * The rule engine discovers, loads, and runs all enabled rules against operations.
 */
interface RuleEngineInterface
{
    /**
     * Execute all enabled rules against the given operations.
     *
     * @param array<int, \Sufyan\MigrationLinter\Support\Operation> $operations
     * @return array<int, \Sufyan\MigrationLinter\Support\Issue> Array of issues found
     */
    public function run(array $operations): array;

    /**
     * Get all loaded rules.
     *
     * @return array<string, RuleInterface> Array of [ruleId => rule]
     */
    public function getRules(): array;

    /**
     * Check if a specific rule is enabled.
     *
     * @param string $ruleId The rule identifier
     * @return bool
     */
    public function isRuleEnabled(string $ruleId): bool;
}
