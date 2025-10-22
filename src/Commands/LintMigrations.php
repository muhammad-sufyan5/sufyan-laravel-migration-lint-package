<?php

namespace Sufyan\MigrationLinter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Sufyan\MigrationLinter\Support\Reporter;
use Sufyan\MigrationLinter\Support\RuleEngine;
use Sufyan\MigrationLinter\Support\MigrationParser;

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
                            {--baseline= : Path to baseline file for ignoring known issues}';

    /**
     * The console command description.
     */
    protected $description = 'Statically analyze migration files for risky schema changes.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Running Laravel Migration Linter...');

        $path = $this->option('path') ?: base_path('database/migrations');

        if (!File::exists($path)) {
            $this->error("Path not found: {$path}");
            return self::FAILURE;
        }

        $parser = new MigrationParser();
        $operations = $parser->parse($path);

        $engine = new RuleEngine();
        $issues = $engine->run($operations);

        // ✅ Baseline Support
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

        // ✅ Generate Baseline if requested
        if ($this->option('generate-baseline')) {
            file_put_contents($baselinePath, json_encode(array_values($issues), JSON_PRETTY_PRINT));
            $this->info("✅ Baseline file generated at: {$baselinePath}");
            return self::SUCCESS;
        }

        // ✅ Show clean output if all ignored
        if (empty($issues)) {
            $this->info("✨ All issues ignored or none found. (Clean after baseline)");
            return self::SUCCESS;
        }

        // ✅ Render final report
        $reporter = new Reporter($this->output);
        $reporter->render($issues, (bool) $this->option('json'));

        $threshold = config('migration-linter.severity_threshold', 'warning');
        return $reporter->exitCode($issues, $threshold);
    }
}
