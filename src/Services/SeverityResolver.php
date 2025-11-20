<?php

namespace Sufyan\MigrationLinter\Services;

use Sufyan\MigrationLinter\Contracts\SeverityResolverInterface;
use Sufyan\MigrationLinter\Contracts\ConfigInterface;

/**
 * Resolves the effective severity level for a rule.
 *
 * Uses a priority-based approach:
 * 1. Custom severity (if explicitly provided)
 * 2. Configured severity (from config file)
 * 3. Default severity ('warning')
 */
class SeverityResolver implements SeverityResolverInterface
{
    /**
     * @param ConfigInterface $config Configuration provider
     */
    public function __construct(private ConfigInterface $config) {}

    /**
     * Resolve the severity level for a rule using priority order.
     *
     * Priority order:
     * 1. Custom severity parameter (highest priority)
     * 2. Severity from configuration
     * 3. Default severity 'warning' (lowest priority)
     *
     * @param string $ruleId The rule identifier
     * @param string|null $customSeverity Optional custom severity override
     * @return string One of: 'error', 'warning', 'info'
     *
     * @example
     * // Returns 'error' (custom severity takes priority)
     * $resolver->resolve('MissingIndexOnForeignKey', 'error');
     *
     * @example
     * // Returns severity from config (if set)
     * $resolver->resolve('MissingIndexOnForeignKey');
     *
     * @example
     * // Returns 'warning' (default fallback)
     * $resolver->resolve('UnknownRule');
     */
    public function resolve(string $ruleId, ?string $customSeverity = null): string
    {
        // Priority 1: Custom severity takes precedence
        if ($customSeverity !== null && $this->isValidSeverity($customSeverity)) {
            return $customSeverity;
        }

        // Priority 2: Check rule configuration
        $ruleConfig = $this->config->getRuleConfig($ruleId);
        if (isset($ruleConfig['severity']) && $this->isValidSeverity($ruleConfig['severity'])) {
            return $ruleConfig['severity'];
        }

        // Priority 3: Return default severity
        return 'warning';
    }

    /**
     * Validate that a severity is one of the allowed values.
     *
     * @param string $severity The severity to validate
     * @return bool True if valid, false otherwise
     */
    private function isValidSeverity(string $severity): bool
    {
        return in_array($severity, ['error', 'warning', 'info'], true);
    }
}
