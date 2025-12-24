<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Formatters;

use Sufyan\MigrationLinter\Formatters\TableFormatter;
use Sufyan\MigrationLinter\Formatters\JsonFormatter;
use Sufyan\MigrationLinter\Formatters\CompactFormatter;
use Sufyan\MigrationLinter\Formatters\SummaryFormatter;
use Sufyan\MigrationLinter\Support\Issue;
use Sufyan\MigrationLinter\Tests\TestCase;

class TableFormatterTest extends TestCase
{
    private TableFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new TableFormatter();
    }

    /** @test */
    public function format_returns_string(): void
    {
        $issues = [
            new Issue('SoftDeletes', 'warning', 'Soft delete on large table', 'migrations/2024_01_01_create_users.php', 10),
        ];

        $result = $this->formatter->format($issues);

        $this->assertIsString($result);
    }

    /** @test */
    public function format_with_no_issues_returns_success_message(): void
    {
        $result = $this->formatter->format([]);

        $this->assertStringContainsString('No issues', $result);
    }

    /** @test */
    public function format_includes_file_name(): void
    {
        $issues = [
            new Issue('Rule', 'warning', 'Message', 'test_file.php', 10),
        ];

        $result = $this->formatter->format($issues);

        $this->assertStringContainsString('test_file.php', $result);
    }

    /** @test */
    public function format_includes_rule_id(): void
    {
        $issues = [
            new Issue('SoftDeletesOnProduction', 'warning', 'Message', 'file.php', 10),
        ];

        $result = $this->formatter->format($issues);

        $this->assertStringContainsString('SoftDeletesOnProduction', $result);
    }

    /** @test */
    public function format_includes_message(): void
    {
        $issues = [
            new Issue('Rule', 'warning', 'Test message content', 'file.php', 10),
        ];

        $result = $this->formatter->format($issues);

        $this->assertStringContainsString('Test message content', $result);
    }

    /** @test */
    public function format_includes_suggestions(): void
    {
        $issue = new Issue('Rule', 'warning', 'Message', 'file.php', 10);
        $issue->suggestion = 'Add an index to improve performance';

        $result = $this->formatter->format([$issue]);

        $this->assertStringContainsString('Suggestion', $result);
        $this->assertStringContainsString('Add an index', $result);
    }

    /** @test */
    public function format_includes_multiple_issues(): void
    {
        $issues = [
            new Issue('Rule1', 'error', 'Error message', 'file1.php', 10),
            new Issue('Rule2', 'warning', 'Warning message', 'file2.php', 20),
            new Issue('Rule3', 'info', 'Info message', 'file3.php', 30),
        ];

        $result = $this->formatter->format($issues);

        $this->assertStringContainsString('Error message', $result);
        $this->assertStringContainsString('Warning message', $result);
        $this->assertStringContainsString('Info message', $result);
    }
}
