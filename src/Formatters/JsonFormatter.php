<?php

namespace Sufyan\MigrationLinter\Formatters;

/**
 * Formats issues as JSON for CI/CD integration.
 *
 * Provides:
 * - Pretty-printed JSON output
 * - All issue fields included
 * - Machine-readable format
 * - Easy parsing by automation tools
 */
class JsonFormatter extends BaseFormatter
{
    /**
     * Format issues as JSON.
     *
     * @param array<int, \Sufyan\MigrationLinter\Support\Issue> $issues
     * @return string JSON formatted output
     */
    public function format(array $issues): string
    {
        $data = array_map(function ($issue) {
            return [
                'rule' => $issue->ruleId,
                'severity' => $issue->severity,
                'message' => $issue->message,
                'file' => $issue->file,
                'line' => $issue->line ?? null,
                'column' => $issue->snippet ?? null,
                'suggestion' => $issue->suggestion ?? null,
                'docs_url' => $issue->docsUrl ?? null,
            ];
        }, $issues);

        return json_encode(
            [
                'issues' => $data,
                'summary' => [
                    'total_issues' => count($issues),
                    'total_files' => $this->countUniqueFiles($issues),
                    'by_severity' => $this->countBySeverity($issues),
                ],
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ) . "\n";
    }
}
