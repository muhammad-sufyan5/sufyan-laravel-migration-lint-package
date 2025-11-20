<?php

namespace Sufyan\MigrationLinter\Formatters;

use Sufyan\MigrationLinter\Contracts\FormatterInterface;
use Sufyan\MigrationLinter\Support\Issue;

/**
 * Abstract base formatter providing common functionality for all formatters.
 *
 * Subclasses implement specific format logic (table, JSON, compact, summary).
 */
abstract class BaseFormatter implements FormatterInterface
{
    /**
     * Format severity level with color for terminal output.
     *
     * @param string $severity The severity level ('error', 'warning', 'info')
     * @return string Formatted severity with color codes
     */
    protected function formatSeverity(string $severity): string
    {
        return match ($severity) {
            'error' => '<fg=red>' . $severity . '</>',
            'warning' => '<fg=yellow>' . $severity . '</>',
            'info' => '<fg=cyan>' . $severity . '</>',
            default => $severity,
        };
    }

    /**
     * Get severity rank for comparison.
     *
     * @param string $severity The severity level
     * @return int Rank (1 = info, 2 = warning, 3 = error)
     */
    protected function getSeverityRank(string $severity): int
    {
        return match ($severity) {
            'info' => 1,
            'warning' => 2,
            'error' => 3,
            default => 0,
        };
    }

    /**
     * Count issues by severity.
     *
     * @param array<int, Issue> $issues The issues to count
     * @return array<string, int> Array with counts for 'error', 'warning', 'info'
     */
    protected function countBySeverity(array $issues): array
    {
        return [
            'error' => count(array_filter($issues, fn($i) => $i->severity === 'error')),
            'warning' => count(array_filter($issues, fn($i) => $i->severity === 'warning')),
            'info' => count(array_filter($issues, fn($i) => $i->severity === 'info')),
        ];
    }

    /**
     * Count unique files affected.
     *
     * @param array<int, Issue> $issues The issues to analyze
     * @return int Number of unique files
     */
    protected function countUniqueFiles(array $issues): int
    {
        return count(array_unique(array_map(fn($i) => $i->file, $issues)));
    }

    /**
     * Truncate string to maximum length.
     *
     * @param string $text Text to truncate
     * @param int $maxLength Maximum length
     * @param string $suffix Suffix to add if truncated
     * @return string Truncated text
     */
    protected function truncate(string $text, int $maxLength, string $suffix = '...'): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }

        return substr($text, 0, $maxLength - strlen($suffix)) . $suffix;
    }

    /**
     * Get terminal width (default 120 if detection fails).
     *
     * @return int Terminal width in characters
     */
    protected function getTerminalWidth(): int
    {
        $width = exec('tput cols');
        return (int) ($width ?: 120);
    }

    /**
     * Filter issues by severity threshold.
     *
     * @param array<int, Issue> $issues All issues
     * @param string $threshold Minimum severity threshold
     * @return array<int, Issue> Filtered issues
     */
    protected function filterBySeverity(array $issues, string $threshold): array
    {
        $minRank = $this->getSeverityRank($threshold);

        return array_filter($issues, fn($issue) => $this->getSeverityRank($issue->severity) >= $minRank);
    }

    /**
     * Sort issues by severity (highest first).
     *
     * @param array<int, Issue> $issues Issues to sort
     * @return array<int, Issue> Sorted issues
     */
    protected function sortBySeverity(array $issues): array
    {
        $sorted = $issues;
        usort($sorted, function (Issue $a, Issue $b) {
            $rankDiff = $this->getSeverityRank($b->severity) - $this->getSeverityRank($a->severity);
            if ($rankDiff !== 0) {
                return $rankDiff;
            }
            // Secondary sort by file name if same severity
            return strcmp($a->file, $b->file);
        });

        return $sorted;
    }
}
