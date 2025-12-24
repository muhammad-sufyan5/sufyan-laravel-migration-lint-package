<?php

namespace Sufyan\MigrationLinter\Services;

use Sufyan\MigrationLinter\Contracts\ParserInterface;
use Sufyan\MigrationLinter\Contracts\RuleEngineInterface;
use Sufyan\MigrationLinter\Contracts\ReporterInterface;
use Illuminate\Support\Facades\File;

/**
 * Orchestrates the entire linting process.
 *
 * This service coordinates parsing, rule execution, baseline filtering,
 * reporting, and exit code determination. It acts as the main entry point
 * for the linting workflow.
 *
 * Uses dependency injection for Parser, RuleEngine, and Reporter,
 * making it highly testable and flexible.
 */
class LintService
{
    /**
     * @param ParserInterface $parser Parser for extracting operations
     * @param RuleEngineInterface $engine Engine for running rules
     * @param ReporterInterface $reporter Reporter for output rendering
     */
    public function __construct(
        private ParserInterface $parser,
        private RuleEngineInterface $engine,
        private ReporterInterface $reporter
    ) {}

    /**
     * Execute the complete linting workflow.
     *
     * Workflow:
     * 1. Parse migration files to extract operations
     * 2. Run all enabled rules against operations
     * 3. Apply baseline filtering (if provided)
     * 4. Generate baseline file (if requested)
     * 5. Report results to output
     * 6. Return appropriate exit code
     *
     * @param string $path Path to migration file or directory
     * @param array<string, mixed> $options Linting options:
     *   - 'baseline': Path to baseline file for filtering known issues
     *   - 'generate_baseline': Whether to create/update baseline file
     *   - 'baseline_path': Path where to save baseline (default: migration-linter-baseline.json)
     *   - 'severity_threshold': Threshold for exit code ('info', 'warning', 'error')
     *   - Other options passed to reporter
     * @return int Exit code (0 = success, 1 = issues found)
     *
     * @throws \InvalidArgumentException If path doesn't exist
     *
     * @example
     * // Basic linting
     * $exitCode = $service->lint(base_path('database/migrations'));
     *
     * @example
     * // Linting with baseline
     * $exitCode = $service->lint(
     *     base_path('database/migrations'),
     *     ['baseline' => 'lint-baseline.json']
     * );
     *
     * @example
     * // Generate baseline from current issues
     * $exitCode = $service->lint(
     *     base_path('database/migrations'),
     *     ['generate_baseline' => true]
     * );
     */
    public function lint(string $path, array $options = []): int
    {
        // Step 1: Validate path exists
        if (!File::exists($path)) {
            throw new \InvalidArgumentException("Path not found: {$path}");
        }

        // Step 2: Parse migration files
        $operations = $this->parser->parse($path);

        // Step 3: Run rules against operations
        $issues = $this->engine->run($operations);

        // Step 4: Filter baseline if provided
        if (isset($options['baseline'])) {
            $issues = $this->filterBaseline($issues, $options['baseline']);
        }

        // Step 5: Generate baseline if requested
        if ($options['generate_baseline'] ?? false) {
            $baselinePath = $options['baseline_path'] ?? 'migration-linter-baseline.json';
            $this->generateBaseline($issues, $baselinePath);
        }

        // Step 6: Report results
        $this->reporter->render($issues, $options);

        // Step 7: Return exit code
        $threshold = $options['severity_threshold'] ?? 'warning';
        return $this->reporter->exitCode($issues, $threshold);
    }

    /**
     * Filter issues against a baseline file.
     *
     * Removes any issues that were previously recorded in the baseline,
     * allowing you to ignore known/legacy issues during linting.
     *
     * @param array $issues Array of issues to filter
     * @param string $baselinePath Path to baseline JSON file
     * @return array Filtered issues (baseline issues removed)
     *
     * @internal
     */
    private function filterBaseline(array $issues, string $baselinePath): array
    {
        // If baseline doesn't exist, return all issues
        if (!File::exists($baselinePath)) {
            return $issues;
        }

        // Decode baseline
        $baseline = json_decode(File::get($baselinePath), true) ?: [];

        // Filter out issues that match baseline entries
        return array_filter($issues, function ($issue) use ($baseline) {
            foreach ($baseline as $known) {
                if (
                    ($known['file'] ?? null) === $issue->file &&
                    ($known['ruleId'] ?? null) === $issue->ruleId &&
                    ($known['message'] ?? null) === $issue->message
                ) {
                    // Issue is in baseline, filter it out
                    return false;
                }
            }
            // Issue not in baseline, keep it
            return true;
        });
    }

    /**
     * Generate or update a baseline file with current issues.
     *
     * Creates a JSON file containing issue fingerprints, allowing
     * you to establish a baseline of known issues and ignore them
     * in future runs.
     *
     * @param array $issues Array of issues to baseline
     * @param string $path Path where to save baseline file
     * @return void
     *
     * @internal
     */
    private function generateBaseline(array $issues, string $path): void
    {
        // Extract issue fingerprints
        $data = array_map(function ($issue) {
            return [
                'file' => $issue->file,
                'ruleId' => $issue->ruleId,
                'message' => $issue->message,
            ];
        }, $issues);

        // Write baseline file
        File::put($path, json_encode(array_values($data), JSON_PRETTY_PRINT));
    }
}
