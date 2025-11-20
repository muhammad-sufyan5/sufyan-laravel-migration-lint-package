<?php

namespace Sufyan\MigrationLinter\Formatters;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

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

        // Table section using Symfony Table component
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
        $output .= $bufferedOutput->fetch();

        // Summary section
        $output .= $this->buildSummaryString($issues);

        return $output;
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
