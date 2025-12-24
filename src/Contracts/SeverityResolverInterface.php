<?php

namespace Sufyan\MigrationLinter\Contracts;

/**
 * Contract for resolving rule severity levels.
 *
 * Implementations determine the effective severity for a rule based on
 * custom overrides and configuration.
 */
interface SeverityResolverInterface
{
    /**
     * Resolve the severity level for a rule.
     *
     * Resolution order:
     * 1. Custom severity (if provided)
     * 2. Configured severity (from config)
     * 3. Default severity ('warning')
     *
     * @param string $ruleId The rule identifier
     * @param string|null $customSeverity Optional custom severity override
     * @return string One of: 'error', 'warning', 'info'
     */
    public function resolve(string $ruleId, ?string $customSeverity = null): string;
}
