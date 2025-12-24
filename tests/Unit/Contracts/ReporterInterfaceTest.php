<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Sufyan\MigrationLinter\Contracts\ReporterInterface;
use Sufyan\MigrationLinter\Support\Issue;

class ReporterInterfaceTest extends TestCase
{
    /** @test */
    public function reporter_interface_has_required_methods(): void
    {
        $this->assertTrue(method_exists(ReporterInterface::class, 'render'));
        $this->assertTrue(method_exists(ReporterInterface::class, 'exitCode'));
    }

    /** @test */
    public function render_method_accepts_issues_and_options(): void
    {
        $reporter = new class implements ReporterInterface {
            public function render(array $issues, array $options = []): void {}
            public function exitCode(array $issues, string $threshold = 'warning'): int { return 0; }
        };

        $issues = [new Issue('TestRule', 'warning', 'Test', 'test.php', 10)];
        $reporter->render($issues, ['format' => 'json']);

        $this->assertTrue(true);
    }

    /** @test */
    public function exit_code_method_returns_integer(): void
    {
        $reporter = new class implements ReporterInterface {
            public function render(array $issues, array $options = []): void {}
            public function exitCode(array $issues, string $threshold = 'warning'): int {
                return count($issues) > 0 ? 1 : 0;
            }
        };

        $issues = [];
        $exitCode = $reporter->exitCode($issues);
        $this->assertEquals(0, $exitCode);

        $issues = [new Issue('TestRule', 'warning', 'Test', 'test.php', 10)];
        $exitCode = $reporter->exitCode($issues);
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function exit_code_respects_threshold(): void
    {
        $reporter = new class implements ReporterInterface {
            public function render(array $issues, array $options = []): void {}
            public function exitCode(array $issues, string $threshold = 'warning'): int {
                $severityLevels = ['info' => 0, 'warning' => 1, 'error' => 2];
                $thresholdLevel = $severityLevels[$threshold] ?? 1;
                
                foreach ($issues as $issue) {
                    if ($severityLevels[$issue->severity] >= $thresholdLevel) {
                        return 1;
                    }
                }
                return 0;
            }
        };

        $warnings = [new Issue('TestRule', 'warning', 'Test', 'test.php', 10)];
        $infos = [new Issue('TestRule', 'info', 'Test', 'test.php', 10)];

        // With 'error' threshold, warnings and infos are ignored
        $this->assertEquals(0, $reporter->exitCode($warnings, 'error'));
        $this->assertEquals(0, $reporter->exitCode($infos, 'error'));

        // With 'warning' threshold, warnings and errors are caught
        $this->assertEquals(1, $reporter->exitCode($warnings, 'warning'));
    }
}
