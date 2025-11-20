<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Services;

use Sufyan\MigrationLinter\Services\LintService;
use Sufyan\MigrationLinter\Contracts\ParserInterface;
use Sufyan\MigrationLinter\Contracts\RuleEngineInterface;
use Sufyan\MigrationLinter\Contracts\ReporterInterface;
use Sufyan\MigrationLinter\Support\Issue;
use Sufyan\MigrationLinter\Tests\TestCase;
use Mockery;

class LintServiceSimpleTest extends TestCase
{
    private LintService $service;
    private ParserInterface $parser;
    private RuleEngineInterface $engine;
    private ReporterInterface $reporter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = Mockery::mock(ParserInterface::class);
        $this->engine = Mockery::mock(RuleEngineInterface::class);
        $this->reporter = Mockery::mock(ReporterInterface::class);

        $this->service = new LintService($this->parser, $this->engine, $this->reporter);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function lint_throws_exception_when_path_does_not_exist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Path not found');

        // This will trigger File::exists() which will fail without Laravel bootstrap
        $this->service->lint('/definitely/does/not/exist');
    }

    /** @test */
    public function lint_orchestrates_parser_engine_reporter(): void
    {
        // Create a real, existing temporary file to avoid File::exists() issues
        $tempFile = tempnam(sys_get_temp_dir(), 'lint_test_');

        try {
            // Setup mock expectations
            $this->parser
                ->shouldReceive('parse')
                ->with($tempFile)
                ->once()
                ->andReturn([]);

            $this->engine
                ->shouldReceive('run')
                ->once()
                ->andReturn([]);

            $this->reporter
                ->shouldReceive('render')
                ->once();

            $this->reporter
                ->shouldReceive('exitCode')
                ->andReturn(0);

            // Call lint - should succeed if orchestration works
            $result = $this->service->lint($tempFile);

            // Verify exit code is returned
            $this->assertEquals(0, $result);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /** @test */
    public function lint_passes_severity_threshold_to_reporter(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'lint_test_');

        try {
            $this->parser->shouldReceive('parse')->andReturn([]);
            $this->engine->shouldReceive('run')->andReturn([]);
            
            $this->reporter
                ->shouldReceive('render')
                ->once();

            $this->reporter
                ->shouldReceive('exitCode')
                ->with([], 'error')
                ->once()
                ->andReturn(1);

            $this->service->lint($tempFile, ['severity_threshold' => 'error']);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function lint_passes_options_to_reporter(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'lint_test_');

        try {
            $options = ['json' => true, 'compact' => false];

            $this->parser->shouldReceive('parse')->andReturn([]);
            $this->engine->shouldReceive('run')->andReturn([]);
            
            $this->reporter
                ->shouldReceive('render')
                ->with([], $options)
                ->once();

            $this->reporter
                ->shouldReceive('exitCode')
                ->andReturn(0);

            $this->service->lint($tempFile, $options);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function lint_returns_exit_code_from_reporter(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'lint_test_');

        try {
            $this->parser->shouldReceive('parse')->andReturn([]);
            $this->engine->shouldReceive('run')->andReturn([]);
            $this->reporter->shouldReceive('render');
            
            $this->reporter
                ->shouldReceive('exitCode')
                ->with([], 'warning')
                ->andReturn(0);

            $exitCode = $this->service->lint($tempFile);
            $this->assertEquals(0, $exitCode);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function lint_calls_parser_with_provided_path(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'lint_test_');

        try {
            $this->parser
                ->shouldReceive('parse')
                ->with($tempFile)
                ->once()
                ->andReturn([]);

            $this->engine->shouldReceive('run')->once()->andReturn([]);
            $this->reporter->shouldReceive('render');
            $this->reporter->shouldReceive('exitCode')->andReturn(0);

            $this->service->lint($tempFile);
        } finally {
            unlink($tempFile);
        }
    }

    /** @test */
    public function lint_with_issues(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'lint_test_');

        try {
            $issues = [
                new Issue('TestRule', 'warning', 'Issue 1', 'migrations/test.php', 10),
            ];

            $this->parser->shouldReceive('parse')->andReturn([]);
            $this->engine->shouldReceive('run')->andReturn($issues);
            $this->reporter->shouldReceive('render');
            $this->reporter->shouldReceive('exitCode')->andReturn(1);

            $exitCode = $this->service->lint($tempFile);
            $this->assertEquals(1, $exitCode);
        } finally {
            unlink($tempFile);
        }
    }
}
