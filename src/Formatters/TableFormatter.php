<?php

namespace Sufyan\MigrationLinter\Formatters;

/**
 * Formats issues as a formatted table for console output.
 *
 * Provides:
 * - Table with columns: File, Rule, Column, Severity, Message
 * - Color-coded severity levels
 * - Suggestions and documentation links
 * - Terminal-aware width adjustment
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

        $terminalWidth = $this->getTerminalWidth();
        $maxMessage = $terminalWidth > 120 ? 80 : 50;
        $maxFile = $terminalWidth > 120 ? 40 : 25;

        // Build table data
        $output .= $this->buildTableString($issues, $maxFile, $maxMessage);

        // Add suggestions
        $output .= $this->buildSuggestionsString($issues);

        return $output;
    }

    /**
     * Build the table string representation.
     *
     * @param array $issues Issues to display
     * @param int $maxFile Max file column width
     * @param int $maxMessage Max message column width
     * @return string Formatted table
     */
    private function buildTableString(array $issues, int $maxFile, int $maxMessage): string
    {
        $output = "";
        $output .= $this->buildTableHeader();

        $sorted = $this->sortBySeverity($issues);

        foreach ($sorted as $issue) {
            $output .= $this->buildTableRow($issue, $maxFile, $maxMessage);
        }

        $output .= $this->buildTableFooter();

        return $output;
    }

    /**
     * Build table header row.
     *
     * @return string Header row
     */
    private function buildTableHeader(): string
    {
        $output = "";
        $output .= $this->padColumn("File", 30) . " ";
        $output .= $this->padColumn("Rule", 25) . " ";
        $output .= $this->padColumn("Column", 8) . " ";
        $output .= $this->padColumn("Severity", 10) . " ";
        $output .= "Message\n";

        $output .= str_repeat("─", 30) . " ";
        $output .= str_repeat("─", 25) . " ";
        $output .= str_repeat("─", 8) . " ";
        $output .= str_repeat("─", 10) . " ";
        $output .= str_repeat("─", 50) . "\n";

        return $output;
    }

    /**
     * Build table row for an issue.
     *
     * @param \Sufyan\MigrationLinter\Support\Issue $issue
     * @param int $maxFile Max file width
     * @param int $maxMessage Max message width
     * @return string Table row
     */
    private function buildTableRow($issue, int $maxFile, int $maxMessage): string
    {
        $output = "";
        $output .= $this->padColumn($this->truncate($issue->file, $maxFile), 30) . " ";
        $output .= $this->padColumn($issue->ruleId, 25) . " ";
        $output .= $this->padColumn($issue->snippet ?? "-", 8) . " ";
        $output .= $this->padColumn($this->formatSeverity($issue->severity), 10) . " ";
        $output .= $this->truncate($issue->message, $maxMessage) . "\n";

        return $output;
    }

    /**
     * Build table footer (separator).
     *
     * @return string Footer row
     */
    private function buildTableFooter(): string
    {
        return "\n";
    }

    /**
     * Pad column to fixed width.
     *
     * @param string $text Text to pad
     * @param int $width Target width
     * @return string Padded text
     */
    private function padColumn(string $text, int $width): string
    {
        return str_pad($text, $width);
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

        foreach ($issues as $index => $issue) {
            if ($issue->suggestion) {
                $suggestionNum = $index + 1;
                $output .= "<fg=cyan>[Suggestion #{$suggestionNum}]</> {$issue->ruleId}:\n";
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
