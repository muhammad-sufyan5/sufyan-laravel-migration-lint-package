<?php

namespace Sufyan\MigrationLinter\Contracts;

/**
 * Contract for configuration management.
 *
 * Implementations provide access to linter configuration (rules, severities, etc.).
 */
interface ConfigInterface
{
    /**
     * Get all rules configuration.
     *
     * @return array<string, array> Array of rule configurations
     */
    public function getRules(): array;

    /**
     * Check if a rule is enabled.
     *
     * @param string $ruleId
     * @return bool
     */
    public function isRuleEnabled(string $ruleId): bool;

    /**
     * Get the global severity threshold.
     *
     * @return string One of: 'info', 'warning', 'error'
     */
    public function getSeverityThreshold(): string;

    /**
     * Get configuration for a specific rule.
     *
     * @param string $ruleId
     * @return array<string, mixed>
     */
    public function getRuleConfig(string $ruleId): array;

    /**
     * Get a configuration value by key.
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;
}
