<?php

namespace Sufyan\MigrationLinter\Support;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

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
     * @return void
     */
    public function render(array $issues, bool $json = false): void
    {
        if ($json) {
            $this->renderJson($issues);
            return;
        }

        if (empty($issues)) {
            $this->output->success('✅ No issues found. Your migrations look safe!');
            return;
        }

        // Add color styles
        $this->addColorStyles();

        $this->output->writeln("\n<options=bold>⚠️  Lint Report</>");
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
                $issue->file,
                $issue->ruleId,
                $issue->snippet ?? '-',  // we'll use this field for column name display
                "<fg={$severityColor}>{$issue->severity}</>",
                $issue->message,
            ];
        }

        $table->setRows($rows);
        $table->render();

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
            'line' => $issue->line,
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
