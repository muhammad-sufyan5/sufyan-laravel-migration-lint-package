<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Formatters;

use Sufyan\MigrationLinter\Formatters\JsonFormatter;
use Sufyan\MigrationLinter\Support\Issue;
use Sufyan\MigrationLinter\Tests\TestCase;

class JsonFormatterTest extends TestCase
{
    private JsonFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new JsonFormatter();
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
    public function format_returns_valid_json(): void
    {
        $issues = [
            new Issue('Rule', 'warning', 'Message', 'file.php', 10),
        ];

        $result = $this->formatter->format($issues);
        $decoded = json_decode($result, true);

        $this->assertIsArray($decoded);
    }

    /** @test */
    public function format_json_includes_issues_array(): void
    {
        $issues = [
            new Issue('Rule1', 'warning', 'Message1', 'file1.php', 10),
            new Issue('Rule2', 'error', 'Message2', 'file2.php', 20),
        ];

        $result = $this->formatter->format($issues);
        $decoded = json_decode($result, true);

        $this->assertArrayHasKey('issues', $decoded);
        $this->assertCount(2, $decoded['issues']);
    }

    /** @test */
    public function format_json_includes_summary(): void
    {
        $issues = [
            new Issue('Rule', 'warning', 'Message', 'file.php', 10),
        ];

        $result = $this->formatter->format($issues);
        $decoded = json_decode($result, true);

        $this->assertArrayHasKey('summary', $decoded);
        $this->assertArrayHasKey('total_issues', $decoded['summary']);
        $this->assertArrayHasKey('total_files', $decoded['summary']);
        $this->assertArrayHasKey('by_severity', $decoded['summary']);
    }

    /** @test */
    public function format_json_summary_counts_issues_correctly(): void
    {
        $issues = [
            new Issue('Rule1', 'error', 'Message1', 'file1.php', 10),
            new Issue('Rule2', 'error', 'Message2', 'file2.php', 20),
            new Issue('Rule3', 'warning', 'Message3', 'file3.php', 30),
        ];

        $result = $this->formatter->format($issues);
        $decoded = json_decode($result, true);

        $this->assertEquals(3, $decoded['summary']['total_issues']);
        $this->assertEquals(2, $decoded['summary']['by_severity']['error']);
        $this->assertEquals(1, $decoded['summary']['by_severity']['warning']);
    }

    /** @test */
    public function format_json_summary_counts_unique_files(): void
    {
        $issues = [
            new Issue('Rule1', 'error', 'Message1', 'file1.php', 10),
            new Issue('Rule2', 'warning', 'Message2', 'file1.php', 20),
            new Issue('Rule3', 'info', 'Message3', 'file2.php', 30),
        ];

        $result = $this->formatter->format($issues);
        $decoded = json_decode($result, true);

        $this->assertEquals(2, $decoded['summary']['total_files']);
    }

    /** @test */
    public function format_json_with_no_issues(): void
    {
        $result = $this->formatter->format([]);
        $decoded = json_decode($result, true);

        $this->assertArrayHasKey('issues', $decoded);
        $this->assertCount(0, $decoded['issues']);
        $this->assertEquals(0, $decoded['summary']['total_issues']);
    }

    /** @test */
    public function format_json_includes_issue_properties(): void
    {
        $issue = new Issue('TestRule', 'error', 'Test message', 'test_file.php', 42);
        $issue->suggestion = 'Add index to column';

        $result = $this->formatter->format([$issue]);
        $decoded = json_decode($result, true);

        $this->assertArrayHasKey('rule', $decoded['issues'][0]);
        $this->assertArrayHasKey('severity', $decoded['issues'][0]);
        $this->assertArrayHasKey('message', $decoded['issues'][0]);
        $this->assertArrayHasKey('file', $decoded['issues'][0]);
        $this->assertArrayHasKey('line', $decoded['issues'][0]);
        $this->assertArrayHasKey('suggestion', $decoded['issues'][0]);
    }
}
