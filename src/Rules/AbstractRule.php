<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

abstract class AbstractRule
{
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
    protected function warn(Operation $operation, string $message): Issue
    {
        return new Issue(
            $this->id(),
            'warning',
            $message,
            $operation->file,
            $operation->line ?? 0,
            $operation->column ?? null
        );
    }
}
