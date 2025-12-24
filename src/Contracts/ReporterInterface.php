<?php

namespace Sufyan\MigrationLinter\Contracts;

/**
 * Contract for reporting results.
 *
 * Implementations handle rendering results to console and determining exit codes.
 */
interface ReporterInterface
{
    /**
     * Render lint results to output.
     *
     * @param array<int, \Sufyan\MigrationLinter\Support\Issue> $issues
     * @param array<string, mixed> $options Renderer options (format, etc.)
     * @return void
     */
    public function render(array $issues, array $options = []): void;

    /**
     * Determine exit code based on issues and severity threshold.
     *
     * @param array<int, \Sufyan\MigrationLinter\Support\Issue> $issues
     * @param string $threshold Severity threshold ('info', 'warning', 'error')
     * @return int Exit code (0 = success, 1 = has issues above threshold)
     */
    public function exitCode(array $issues, string $threshold = 'warning'): int;
}
