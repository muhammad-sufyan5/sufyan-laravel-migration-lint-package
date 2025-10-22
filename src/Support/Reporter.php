<?php

namespace Sufyan\MigrationLinter\Support;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Illuminate\Support\Str;

class Reporter
{
    public function __construct(
        protected OutputStyle $output
    ) {}

    /**
     * Render lint results to console or JSON.
     *
     * @param  Issue[]  $issues
     * @param  bool  $json
     * @param  bool  $compact
     * @return void
     */
    public function render(array $issues, bool $json = false, bool $compact = false): void
    {
        if ($json) {
            $this->renderJson($issues);
            return;
        }

        if (empty($issues)) {
            $this->output->success('✅ No issues found. Your migrations look safe!');
            return;
        }

        $this->addColorStyles();

        // Compact mode
        if ($compact) {
            $this->renderCompact($issues);
            return;
        }

        $this->renderTable($issues);
    }

    /**
     * Render as formatted table with truncation for small terminals.
     */
    protected function renderTable(array $issues): void
    {
        $this->output->writeln("\n<options=bold>⚠️  Lint Report</>");

        $terminalWidth = (int) (exec('tput cols') ?: 120);
        $maxMessage = $terminalWidth > 120 ? 80 : 50;
        $maxFile = $terminalWidth > 120 ? 40 : 25;

        $table = new Table($this->output);
        $table->setHeaders(['File', 'Rule', 'Column', 'Severity', 'Message']);

        $rows = [];
        foreach ($issues as $issue) {
            $severityColor = match ($issue->severity) {
                'error' => 'red',
                'warning' => 'yellow',
                'info' => 'cyan',
                default => 'white',
            };

            $rows[] = [
                Str::limit($issue->file, $maxFile),
                $issue->ruleId,
                $issue->snippet ?? '-',
                "<fg={$severityColor}>{$issue->severity}</>",
                Str::limit($issue->message, $maxMessage),
            ];
        }

        $table->setRows($rows);
        $table->render();

        $this->output->newLine();
        $this->output->writeln("<comment>Found " . count($issues) . " issue(s)</comment>");
    }

    /**
     * Render compact single-line mode.
     */
    protected function renderCompact(array $issues): void
    {
        $this->output->writeln("⚠️ Compact Lint Report\n");

        foreach ($issues as $issue) {
            $color = match ($issue->severity) {
                'error' => 'red',
                'warning' => 'yellow',
                'info' => 'cyan',
                default => 'white',
            };

            $this->output->writeln(
                "• <fg={$color}>[{$issue->severity}]</> {$issue->ruleId} — {$issue->message} ({$issue->file})"
            );
        }

        $this->output->newLine();
        $this->output->writeln("<comment>Found " . count($issues) . " issue(s)</comment>");
    }

    /**
     * Render JSON output for CI/CD.
     */
    protected function renderJson(array $issues): void
    {
        $jsonData = array_map(fn($issue) => [
            'rule' => $issue->ruleId,
            'severity' => $issue->severity,
            'message' => $issue->message,
            'file' => $issue->file,
            'line' => $issue->line ?? null,
        ], $issues);

        $this->output->writeln(json_encode($jsonData, JSON_PRETTY_PRINT));
    }

    /**
     * Determine the exit code based on severity threshold.
     */
    public function exitCode(array $issues, string $threshold = 'warning'): int
    {
        if (empty($issues)) {
            return 0;
        }

        $severityRank = ['info' => 1, 'warning' => 2, 'error' => 3];
        $minRank = $severityRank[$threshold] ?? 2;

        foreach ($issues as $issue) {
            if (($severityRank[$issue->severity] ?? 0) >= $minRank) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * Add custom color styles for better visuals.
     */
    protected function addColorStyles(): void
    {
        $styles = [
            'red' => new OutputFormatterStyle('red'),
            'yellow' => new OutputFormatterStyle('yellow'),
            'cyan' => new OutputFormatterStyle('cyan'),
            'white' => new OutputFormatterStyle('white'),
        ];

        foreach ($styles as $name => $style) {
            if (! $this->output->getFormatter()->hasStyle($name)) {
                $this->output->getFormatter()->setStyle($name, $style);
            }
        }
    }
}
