<?php

namespace Sufyan\MigrationLinter\Services;

use Sufyan\MigrationLinter\Contracts\ConfigInterface;
use Illuminate\Config\Repository as ConfigRepository;

/**
 * Provides access to Laravel configuration for the migration linter.
 *
 * This class bridges the gap between Laravel's config system and the linter's
 * ConfigInterface contract, allowing dependency injection throughout the application.
 */
class LaravelConfigProvider implements ConfigInterface
{
    /**
     * @param ConfigRepository $config Laravel's configuration repository
     */
    public function __construct(private ConfigRepository $config) {}

    /**
     * Get all rules configuration.
     *
     * @return array<string, array> Array mapping rule IDs to their configurations
     */
    public function getRules(): array
    {
        return $this->config->get('migration-linter.rules', []);
    }

    /**
     * Check if a specific rule is enabled.
     *
     * @param string $ruleId The rule identifier
     * @return bool True if enabled, false otherwise
     */
    public function isRuleEnabled(string $ruleId): bool
    {
        return $this->getRuleConfig($ruleId)['enabled'] ?? false;
    }

    /**
     * Get the global severity threshold for the linter.
     *
     * @return string One of: 'info', 'warning', 'error'
     */
    public function getSeverityThreshold(): string
    {
        return $this->config->get('migration-linter.severity_threshold', 'warning');
    }

    /**
     * Get configuration for a specific rule.
     *
     * @param string $ruleId The rule identifier
     * @return array<string, mixed> Rule configuration array
     */
    public function getRuleConfig(string $ruleId): array
    {
        return $this->getRules()[$ruleId] ?? [];
    }

    /**
     * Get a configuration value by key with optional default.
     *
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $default Default value if key not found
     * @return mixed The configuration value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config->get('migration-linter.' . $key, $default);
    }
}
