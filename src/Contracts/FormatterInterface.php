<?php

namespace Sufyan\MigrationLinter\Contracts;

/**
 * Contract for formatting output.
 *
 * Implementations convert issues into formatted strings for display.
 */
interface FormatterInterface
{
    /**
     * Format issues for display.
     *
     * @param array<int, \Sufyan\MigrationLinter\Support\Issue> $issues
     * @return string Formatted output string
     */
    public function format(array $issues): string;
}
