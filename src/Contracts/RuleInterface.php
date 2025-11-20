<?php

namespace Sufyan\MigrationLinter\Contracts;

use Sufyan\MigrationLinter\Support\Operation;

/**
 * Contract for all linting rules.
 *
 * Each rule must implement this interface to be discoverable and executable
 * by the RuleEngine.
 */
interface RuleInterface
{
    /**
     * Unique identifier for this rule (e.g., 'AddNonNullableColumnWithoutDefault').
     *
     * @return string
     */
    public function id(): string;

    /**
     * Human-readable description of what this rule checks.
     *
     * @return string
     */
    public function description(): string;

    /**
     * Check the given operation for violations.
     *
     * @param Operation $operation The operation to check
     * @return array<\Sufyan\MigrationLinter\Support\Issue> Array of issues found (empty if none)
     */
    public function check(Operation $operation): array;

    /**
     * Get the severity level for this rule.
     *
     * @return string One of: 'error', 'warning', 'info'
     */
    public function severity(): string;
}
