<?php

namespace Sufyan\MigrationLinter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Sufyan\MigrationLinter\Contracts\SeverityResolverInterface;
use Sufyan\MigrationLinter\Formatters\TableFormatter;
use Sufyan\MigrationLinter\Formatters\JsonFormatter;
use Sufyan\MigrationLinter\Formatters\CompactFormatter;
use Sufyan\MigrationLinter\Formatters\SummaryFormatter;
use Sufyan\MigrationLinter\Support\MigrationParser;
use Sufyan\MigrationLinter\Support\RuleEngine;

class LintMigrations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrate:lint 
                            {--generate-baseline : Create json file to skip existing migrations}
                            {--compact : Display simplified text-based output for small terminals}
                            {--path= : Path to a specific migration file or folder}
                            {--json : Output results in JSON format}
                            {--baseline= : Path to baseline file for ignoring known issues}
                            {--rules : Display available rules and exit}
                            {--summary : Display summary footer in output}';

    /**
     * The console command description.
     */
    protected $description = 'Statically analyze migration files for risky schema changes.';

    /**
     * Optional severity resolver (for DI).
     */
    protected ?SeverityResolverInterface $severityResolver = null;

    /**
     * Constructor to support dependency injection.
     *
     * @param SeverityResolverInterface|null $severityResolver
     */
    public function __construct(?SeverityResolverInterface $severityResolver = null)
    {
        parent::__construct();
        $this->severityResolver = $severityResolver;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // ✅ Handle --rules flag
        if ($this->option('rules')) {
            return $this->listRules();
        }

        $this->info('🔍 Running Laravel Migration Linter...');

        $path = $this->option('path') ?: base_path('database/migrations');

        if (!File::exists($path)) {
            $this->error("Path not found: {$path}");
            return self::FAILURE;
        }

        // ✅ Phase 6: Resolve dependencies from container
        $parser = app(MigrationParser::class);
        $engine = app(RuleEngine::class, ['severityResolver' => $this->severityResolver]);

        $operations = $parser->parse($path);
        $issues = $engine->run($operations);

        // Baseline Support
        $baselinePath = base_path('migration-linter-baseline.json');
        $baseline = [];

        if (file_exists($baselinePath)) {
            $baseline = json_decode(file_get_contents($baselinePath), true) ?: [];
        }

        // Ignore known baseline issues
        $issues = array_filter($issues, function ($issue) use ($baseline) {
            foreach ($baseline as $known) {
                if (
                    ($known['file'] ?? null) === $issue->file &&
                    ($known['ruleId'] ?? null) === $issue->ruleId &&
                    ($known['message'] ?? null) === $issue->message
                ) {
                    return false; // Skip baseline issues
                }
            }
            return true;
        });

        // Generate Baseline if requested
        if ($this->option('generate-baseline')) {
            file_put_contents($baselinePath, json_encode(array_values($issues), JSON_PRETTY_PRINT));
            $this->info("Baseline file generated at: {$baselinePath}");
            return self::SUCCESS;
        }

        // Show clean output if all ignored
        if (empty($issues)) {
            $this->info("✨ All issues ignored or none found. (Clean after baseline)");
            return self::SUCCESS;
        }

        // ✅ Phase 6: Use new Formatters instead of Reporter
        $formatter = $this->selectFormatter();
        $output = $formatter->format($issues);
        $this->output->write($output);

        // Determine exit code based on severity threshold
        $threshold = config('migration-linter.severity_threshold', 'warning');
        return $this->determineExitCode($issues, $threshold);
    }

    /**
     * Select the appropriate formatter based on command options.
     *
     * @return \Sufyan\MigrationLinter\Contracts\FormatterInterface
     */
    protected function selectFormatter()
    {
        if ($this->option('json')) {
            return new JsonFormatter();
        }

        if ($this->option('compact')) {
            return new CompactFormatter();
        }

        if ($this->option('summary')) {
            return new SummaryFormatter();
        }

        // Default: TableFormatter
        return new TableFormatter();
    }

    /**
     * Determine exit code based on issues and severity threshold.
     *
     * @param array $issues
     * @param string $threshold
     * @return int
     */
    protected function determineExitCode(array $issues, string $threshold): int
    {
        if (empty($issues)) {
            return self::SUCCESS;
        }

        // Check if any issue meets or exceeds threshold
        $severityRank = ['info' => 1, 'warning' => 2, 'error' => 3];
        $thresholdRank = $severityRank[$threshold] ?? 2;

        foreach ($issues as $issue) {
            $issueRank = $severityRank[$issue->severity] ?? 1;
            if ($issueRank >= $thresholdRank) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    protected function listRules(): int
    {
        $this->info('📋 Available Migration Linter Rules');
        $this->newLine();

        $rules = config('migration-linter.rules', []);

        $rows = [];

        foreach ($rules as $ruleId => $settings) {
            $enabled = $settings['enabled'] ? 'Yes' : 'No';

            $description = '—';
            $className = "Sufyan\\MigrationLinter\\Rules\\{$ruleId}";
            if (class_exists($className)) {
                $ruleInstance = new $className();
                if (method_exists($ruleInstance, 'description')) {
                    $description = $ruleInstance->description();
                }
            }

            $rows[] = [
                $ruleId,
                $enabled,
                $description,
            ];
        }

        $this->table(['Rule ID', 'Enabled', 'Description'], $rows);
        return self::SUCCESS;
    }
}
