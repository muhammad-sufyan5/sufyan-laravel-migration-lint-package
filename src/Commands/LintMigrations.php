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

        $reporter = new Reporter($this->output);
        $reporter->render($issues, (bool)$this->option('json'));

        $threshold = config('migration-linter.severity_threshold', 'warning');
        return $reporter->exitCode($issues, $threshold);
    }
}
