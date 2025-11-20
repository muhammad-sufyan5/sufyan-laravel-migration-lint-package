<?php

namespace Sufyan\MigrationLinter\Formatters;

/**
 * Formats issues in compact single-line mode.
 *
 * Provides:
 * - One issue per line
 * - Minimal formatting
 * - Good for CI/CD logs
 * - Easy to grep and parse
 */
class CompactFormatter extends BaseFormatter
{
    /**
     * Format issues in compact mode.
     *
     * @param array<int, \Sufyan\MigrationLinter\Support\Issue> $issues
     * @return string Compact formatted output
     */
    public function format(array $issues): string
    {
        if (empty($issues)) {
            return "✅ No issues found.\n";
        }

        $output = "⚠️ Compact Lint Report\n\n";

        $sorted = $this->sortBySeverity($issues);

        foreach ($sorted as $issue) {
            $color = match ($issue->severity) {
                'error' => 'red',
                'warning' => 'yellow',
                'info' => 'cyan',
                default => 'white',
            };

            $output .= sprintf(
                "• <fg=%s>[%s]</> %s — %s (%s)\n",
                $color,
                $issue->severity,
                $issue->ruleId,
                $issue->message,
                $issue->file
            );
        }

        $output .= sprintf("\n<comment>Found %d issue(s)</comment>\n", count($issues));

        return $output;
    }
}
