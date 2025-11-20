<?php

namespace Sufyan\MigrationLinter\Formatters;

/**
 * Formats issues with detailed summary statistics.
 *
 * Provides:
 * - Table format with all issue details
 * - Comprehensive summary statistics
 * - Severity breakdown
 * - Status indicators
 */
class SummaryFormatter extends BaseFormatter
{
    /**
     * Format issues with summary statistics.
     *
     * @param array<int, \Sufyan\MigrationLinter\Support\Issue> $issues
     * @return string Formatted output with summary
     */
    public function format(array $issues): string
    {
        if (empty($issues)) {
            return "✅ No issues found. Your migrations look safe!\n";
        }

        $output = "\n<options=bold>⚠️  Lint Report with Summary</>\n\n";

        // Table section
        $terminalWidth = $this->getTerminalWidth();
        $maxMessage = $terminalWidth > 120 ? 80 : 50;
        $maxFile = $terminalWidth > 120 ? 40 : 25;

        $output .= $this->buildTableString($issues, $maxFile, $maxMessage);

        // Summary section
        $output .= $this->buildSummaryString($issues);

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
     * Build table footer.
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
     * Build summary section with statistics.
     *
     * @param array $issues Issues to summarize
     * @return string Formatted summary
     */
    private function buildSummaryString(array $issues): string
    {
        $output = "<options=bold>📊 Summary</>\n";
        $output .= str_repeat('─', 35) . "\n\n";

        $totalFiles = $this->countUniqueFiles($issues);
        $totalIssues = count($issues);
        $severityCounts = $this->countBySeverity($issues);

        $output .= sprintf("🧩 Total Files Scanned:     <info>%d</info>\n", $totalFiles);
        $output .= sprintf("🔍 Issues Found:            <comment>%d</comment>\n", $totalIssues);
        $output .= sprintf("⚠️  Warnings:               <comment>%d</comment>\n", $severityCounts['warning']);
        $output .= sprintf("❌ Errors:                  <error>%d</error>\n", $severityCounts['error']);
        $output .= sprintf("💡 Info:                    <fg=cyan>%d</>\n", $severityCounts['info']);
        $output .= "\n";

        // Status indicator
        if ($severityCounts['error'] > 0) {
            $output .= "<error>❗ Some migrations may be unsafe. Please review before deploying.</error>\n";
        } elseif ($severityCounts['warning'] > 0) {
            $output .= "<comment>⚠️  Some migrations contain potential risks.</comment>\n";
        } else {
            $output .= "<info>✅ All migrations look safe!</info>\n";
        }

        $output .= "\n";

        return $output;
    }
}
