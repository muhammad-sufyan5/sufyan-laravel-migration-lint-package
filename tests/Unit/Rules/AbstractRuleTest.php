<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Rules;

use Mockery\Mock;
use Sufyan\MigrationLinter\Contracts\SeverityResolverInterface;
use Sufyan\MigrationLinter\Rules\AddNonNullableColumnWithoutDefault;
use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Tests\TestCase;

class AbstractRuleTest extends TestCase
{
    private AddNonNullableColumnWithoutDefault $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new AddNonNullableColumnWithoutDefault();
    }

    /** @test */
    public function it_implements_rule_interface(): void
    {
        $this->assertTrue(method_exists($this->rule, 'id'));
        $this->assertTrue(method_exists($this->rule, 'description'));
        $this->assertTrue(method_exists($this->rule, 'check'));
        $this->assertTrue(method_exists($this->rule, 'severity'));
    }

    /** @test */
    public function it_has_id_method(): void
    {
        $id = $this->rule->id();

        $this->assertIsString($id);
        $this->assertNotEmpty($id);
    }

    /** @test */
    public function it_has_description_method(): void
    {
        $description = $this->rule->description();

        $this->assertIsString($description);
        $this->assertNotEmpty($description);
    }

    /** @test */
    public function it_returns_default_severity_warning(): void
    {
        $severity = $this->rule->severity();

        $this->assertEquals('warning', $severity);
    }

    /** @test */
    public function it_respects_custom_severity(): void
    {
        $this->rule->customSeverity = 'error';

        $severity = $this->rule->severity();

        $this->assertEquals('error', $severity);
    }

    /** @test */
    public function it_uses_severity_resolver_when_available(): void
    {
        // Mock the SeverityResolverInterface
        $resolver = \Mockery::mock(SeverityResolverInterface::class);
        $resolver->shouldReceive('resolve')
            ->with('AddNonNullableColumnWithoutDefault', null)
            ->once()
            ->andReturn('info');

        // Inject resolver
        $this->rule->setSeverityResolver($resolver);

        $severity = $this->rule->severity();

        $this->assertEquals('info', $severity);
    }

    /** @test */
    public function it_passes_custom_severity_to_resolver(): void
    {
        // Mock the SeverityResolverInterface
        $resolver = \Mockery::mock(SeverityResolverInterface::class);
        $resolver->shouldReceive('resolve')
            ->with('AddNonNullableColumnWithoutDefault', 'error')
            ->once()
            ->andReturn('error');

        // Inject resolver and set custom severity
        $this->rule->setSeverityResolver($resolver);
        $this->rule->customSeverity = 'error';

        $severity = $this->rule->severity();

        $this->assertEquals('error', $severity);
    }

    /** @test */
    public function it_can_set_severity_resolver(): void
    {
        $resolver = \Mockery::mock(SeverityResolverInterface::class);

        // Should not throw exception
        $this->rule->setSeverityResolver($resolver);

        $this->assertTrue(true);
    }

    /** @test */
    public function check_method_returns_array_of_issues(): void
    {
        $operation = new Operation(
            table: 'users',
            method: 'string',
            args: '',
            file: 'migrations/2024_01_01_create_users_table.php',
            path: 'database/migrations',
            line: 20,
            rawCode: "->string('email')",
            column: 'email'
        );

        $issues = $this->rule->check($operation);

        $this->assertIsArray($issues);
    }

    /** @test */
    public function warn_helper_creates_issue_with_correct_severity(): void
    {
        $this->rule->customSeverity = 'error';

        $operation = new Operation(
            table: 'users',
            method: 'string',
            args: '',
            file: 'test.php',
            path: 'migrations',
            line: 10,
            rawCode: "->string('name')",
            column: 'name'
        );

        // Use reflection to call protected method
        $reflection = new \ReflectionClass($this->rule);
        $method = $reflection->getMethod('warn');
        $method->setAccessible(true);

        $issue = $method->invoke(
            $this->rule,
            $operation,
            'Test message',
            'name',
            'Fix this',
            'https://example.com'
        );

        $this->assertEquals('error', $issue->severity);
        $this->assertEquals('AddNonNullableColumnWithoutDefault', $issue->ruleId);
        $this->assertEquals('Test message', $issue->message);
    }

    /** @test */
    public function it_maintains_backward_compatibility(): void
    {
        // Without SeverityResolver, should use legacy pattern
        $this->rule->customSeverity = null;

        $severity = $this->rule->severity();

        // Should return 'warning' from defaultSeverity() method
        $this->assertNotNull($severity);
    }
}
