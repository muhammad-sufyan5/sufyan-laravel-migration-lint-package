<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

abstract class AbstractRule
{
    /**
     * Default severity level for this rule.
     */
    public ?string $customSeverity = null;

    public function severity(): string
    {
        // ✅ Priority: customSeverity (from config) > class-defined default > fallback warning
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
