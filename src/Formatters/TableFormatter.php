<?php

namespace Sufyan\MigrationLinter\Formatters;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Formats issues as a formatted table for console output.
 *
 * Uses Symfony's Table helper for proper alignment and formatting.
 */
class TableFormatter extends BaseFormatter
{
    /**
     * Format issues as a table.
     *
     * @param array<int, \Sufyan\MigrationLinter\Support\Issue> $issues
     * @return string Formatted table output
     */
    public function format(array $issues): string
    {
        if (empty($issues)) {
            return "✅ No issues found. Your migrations look safe!\n";
        }

        $output = "\n<options=bold>⚠️  Lint Report</>\n\n";

        // Create buffered output to capture table
        $bufferedOutput = new BufferedOutput();
        $table = new Table($bufferedOutput);
        $table->setHeaders(['File', 'Rule', 'Column', 'Severity', 'Message']);

        $sorted = $this->sortBySeverity($issues);

        foreach ($sorted as $issue) {
            $table->addRow([
                $this->truncate($issue->file, 30),
                $this->truncate($issue->ruleId, 25),
                $issue->snippet ?? '-',
                $this->formatSeverity($issue->severity),
                $this->truncate($issue->message, 60),
            ]);
        }

        $table->render();
        $tableOutput = $bufferedOutput->fetch();
        $output .= $tableOutput;

        // Add suggestions
        $output .= $this->buildSuggestionsString($issues);

        return $output;
    }

    /**
     * Build suggestions section.
     *
     * @param array $issues Issues with suggestions
     * @return string Formatted suggestions
     */
    private function buildSuggestionsString(array $issues): string
    {
        $output = "";
        $suggestionCount = 0;

        foreach ($issues as $issue) {
            if ($issue->suggestion) {
                $suggestionCount++;
                $output .= "<fg=cyan>[Suggestion #{$suggestionCount}]</> {$issue->ruleId}:\n";
                $output .= "  {$issue->suggestion}\n";

                if ($issue->docsUrl) {
                    $output .= "  📖 Learn more: {$issue->docsUrl}\n";
                }

                $output .= "\n";
            }
        }

        return $output;
    }
}
