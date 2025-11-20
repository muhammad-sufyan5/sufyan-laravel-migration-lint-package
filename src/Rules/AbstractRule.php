<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Contracts\RuleInterface;
use Sufyan\MigrationLinter\Contracts\SeverityResolverInterface;
use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

abstract class AbstractRule implements RuleInterface
{
    /**
     * Optional custom severity override for this rule instance.
     */
    public ?string $customSeverity = null;

    /**
     * Severity resolver for determining effective severity level.
     */
    protected ?SeverityResolverInterface $severityResolver = null;

    /**
     * Set the severity resolver (for dependency injection).
     *
     * @param SeverityResolverInterface $resolver
     * @return void
     */
    public function setSeverityResolver(SeverityResolverInterface $resolver): void
    {
        $this->severityResolver = $resolver;
    }

    /**
     * Get the severity level for this rule.
     *
     * Uses SeverityResolverInterface if available (Phase 4+), otherwise falls back
     * to the legacy pattern for backward compatibility.
     *
     * @return string One of: 'error', 'warning', 'info'
     */
    public function severity(): string
    {
        // ✅ Phase 4: Use injected SeverityResolverInterface if available
        if ($this->severityResolver) {
            return $this->severityResolver->resolve($this->id(), $this->customSeverity);
        }

        // ✅ Fallback: Legacy pattern for backward compatibility
        if ($this->customSeverity) {
            return $this->customSeverity;
        }

        if (method_exists($this, 'defaultSeverity')) {
            return $this->defaultSeverity();
        }

        return 'warning';
    }

    /**
     * A unique ID for this rule.
     */
    abstract public function id(): string;

    /**
     * Description of what this rule checks for.
     */
    abstract public function description(): string;

    /**
     * Check a single operation and return an array of Issue objects.
     *
     * @param Operation $operation
     * @return Issue[]
     */
    abstract public function check(Operation $operation): array;

    /**
     * Helper: create a warning Issue quickly.
     */
    // ⬇️ Accept optional $column, $suggestion, and $docsUrl for enhanced feedback
    protected function warn(
        Operation $operation,
        string $message,
        ?string $column = null,
        ?string $suggestion = null,
        ?string $docsUrl = null
    ): Issue {
        return new Issue(
            $this->id(),
            $this->severity(),
            $message,
            $operation->file,
            $operation->line ?? 0,
            $column ?? $operation->column,
            $suggestion,
            $docsUrl
        );
    }
}
