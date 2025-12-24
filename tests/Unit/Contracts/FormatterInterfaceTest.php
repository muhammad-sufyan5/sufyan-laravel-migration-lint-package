<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Sufyan\MigrationLinter\Contracts\FormatterInterface;
use Sufyan\MigrationLinter\Support\Issue;

class FormatterInterfaceTest extends TestCase
{
    /** @test */
    public function formatter_interface_has_format_method(): void
    {
        $this->assertTrue(method_exists(FormatterInterface::class, 'format'));
    }

    /** @test */
    public function format_method_accepts_issues_array(): void
    {
        $formatter = new class implements FormatterInterface {
            public function format(array $issues): string {
                return '';
            }
        };

        $issues = [
            new Issue('TestRule', 'warning', 'Test message', 'test.php', 10),
        ];

        $result = $formatter->format($issues);
        $this->assertIsString($result);
    }

    /** @test */
    public function format_method_handles_empty_array(): void
    {
        $formatter = new class implements FormatterInterface {
            public function format(array $issues): string {
                return 'No issues found';
            }
        };

        $result = $formatter->format([]);
        $this->assertEquals('No issues found', $result);
    }

    /** @test */
    public function format_method_returns_string(): void
    {
        $formatter = new class implements FormatterInterface {
            public function format(array $issues): string {
                return json_encode($issues);
            }
        };

        $result = $formatter->format([]);
        $this->assertIsString($result);
    }
}
