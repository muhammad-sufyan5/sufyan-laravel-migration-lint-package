<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Formatters;

use Sufyan\MigrationLinter\Formatters\CompactFormatter;
use Sufyan\MigrationLinter\Support\Issue;
use Sufyan\MigrationLinter\Tests\TestCase;

class CompactFormatterTest extends TestCase
{
    private CompactFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new CompactFormatter();
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
    public function format_one_line_per_issue(): void
    {
        $issues = [
            new Issue('Rule1', 'error', 'Error message', 'file1.php', 10),
            new Issue('Rule2', 'warning', 'Warning message', 'file2.php', 20),
            new Issue('Rule3', 'info', 'Info message', 'file3.php', 30),
        ];

        $result = $this->formatter->format($issues);

        // Count lines with bullet point (issue lines)
        $issueLines = array_filter(explode("\n", $result), fn($line) => str_contains($line, '•'));

        $this->assertEquals(3, count($issueLines));
    }

    /** @test */
    public function format_includes_severity_indicator(): void
    {
        $issues = [
            new Issue('Rule', 'error', 'Message', 'file.php', 10),
        ];

        $result = $this->formatter->format($issues);

        $this->assertStringContainsString('error', $result);
    }

    /** @test */
    public function format_multiple_issues_from_same_file(): void
    {
        $issues = [
            new Issue('Rule1', 'error', 'Error 1', 'file.php', 10),
            new Issue('Rule2', 'warning', 'Warning 1', 'file.php', 20),
        ];

        $result = $this->formatter->format($issues);

        $this->assertStringContainsString('Rule1', $result);
        $this->assertStringContainsString('Rule2', $result);
        $this->assertStringContainsString('file.php', $result);
    }
}
