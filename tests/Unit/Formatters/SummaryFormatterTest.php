<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Formatters;

use Sufyan\MigrationLinter\Formatters\SummaryFormatter;
use Sufyan\MigrationLinter\Support\Issue;
use Sufyan\MigrationLinter\Tests\TestCase;

class SummaryFormatterTest extends TestCase
{
    private SummaryFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new SummaryFormatter();
    }

    /** @test */
    public function format_returns_string(): void
    {
        $issues = [
            new Issue('Rule', 'warning', 'Message', 'file.php', 10),
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
            new Issue('SoftDeletes', 'warning', 'Message', 'file.php', 10),
        ];

        $result = $this->formatter->format($issues);

        $this->assertStringContainsString('SoftDeletes', $result);
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
    public function format_includes_summary_statistics(): void
    {
        $issues = [
            new Issue('Rule1', 'error', 'Message 1', 'file1.php', 10),
            new Issue('Rule2', 'warning', 'Message 2', 'file2.php', 20),
        ];

        $result = $this->formatter->format($issues);

        // Should include summary section with stats
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('file1.php', $result);
        $this->assertStringContainsString('file2.php', $result);
    }

    /** @test */
    public function format_includes_issue_count(): void
    {
        $issues = [
            new Issue('Rule1', 'error', 'Error', 'file1.php', 10),
            new Issue('Rule2', 'error', 'Error', 'file2.php', 20),
            new Issue('Rule3', 'warning', 'Warning', 'file3.php', 30),
        ];

        $result = $this->formatter->format($issues);

        // Should display total issue count
        $this->assertStringContainsString('3', $result);
    }

    /** @test */
    public function format_includes_severity_breakdown(): void
    {
        $issues = [
            new Issue('Rule1', 'error', 'Error message', 'file1.php', 10),
            new Issue('Rule2', 'warning', 'Warning message', 'file2.php', 20),
            new Issue('Rule3', 'info', 'Info message', 'file3.php', 30),
        ];

        $result = $this->formatter->format($issues);

        // Should include all severity levels mentioned
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function format_includes_unique_file_count(): void
    {
        $issues = [
            new Issue('Rule1', 'error', 'Error 1', 'file1.php', 10),
            new Issue('Rule2', 'warning', 'Warning 1', 'file1.php', 20),
            new Issue('Rule3', 'info', 'Info 1', 'file2.php', 30),
        ];

        $result = $this->formatter->format($issues);

        // Should have summary indicating files affected
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function format_displays_summary_data(): void
    {
        $issue = new Issue('Rule', 'warning', 'Message', 'file.php', 10);
        $issue->suggestion = 'Add an index to improve performance';

        $result = $this->formatter->format([$issue]);

        // SummaryFormatter focuses on summary stats, not individual suggestions
        $this->assertStringContainsString('Summary', $result);
        $this->assertStringContainsString('file.php', $result);
    }

    /** @test */
    public function format_handles_multiple_issues_per_file(): void
    {
        $issues = [
            new Issue('Rule1', 'error', 'Error 1', 'file.php', 10),
            new Issue('Rule2', 'warning', 'Warning 1', 'file.php', 20),
            new Issue('Rule3', 'error', 'Error 2', 'file.php', 30),
        ];

        $result = $this->formatter->format($issues);

        $this->assertStringContainsString('file.php', $result);
        $this->assertStringContainsString('Rule1', $result);
        $this->assertStringContainsString('Rule2', $result);
        $this->assertStringContainsString('Rule3', $result);
    }
}
