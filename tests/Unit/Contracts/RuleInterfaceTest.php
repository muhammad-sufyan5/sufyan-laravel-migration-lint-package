<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Sufyan\MigrationLinter\Contracts\RuleInterface;
use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

/**
 * Tests verify that the RuleInterface contract is correctly defined.
 */
class RuleInterfaceTest extends TestCase
{
    /** @test */
    public function rule_interface_has_required_methods(): void
    {
        $this->assertTrue(method_exists(RuleInterface::class, 'id'));
        $this->assertTrue(method_exists(RuleInterface::class, 'description'));
        $this->assertTrue(method_exists(RuleInterface::class, 'check'));
        $this->assertTrue(method_exists(RuleInterface::class, 'severity'));
    }

    /** @test */
    public function id_method_returns_string(): void
    {
        $rule = new class implements RuleInterface {
            public function id(): string { return 'TestRule'; }
            public function description(): string { return 'Test'; }
            public function check(Operation $op): array { return []; }
            public function severity(): string { return 'warning'; }
        };

        $this->assertIsString($rule->id());
        $this->assertEquals('TestRule', $rule->id());
    }

    /** @test */
    public function description_method_returns_string(): void
    {
        $rule = new class implements RuleInterface {
            public function id(): string { return 'TestRule'; }
            public function description(): string { return 'A test rule'; }
            public function check(Operation $op): array { return []; }
            public function severity(): string { return 'warning'; }
        };

        $this->assertIsString($rule->description());
        $this->assertEquals('A test rule', $rule->description());
    }

    /** @test */
    public function check_method_returns_array_of_issues(): void
    {
        $rule = new class implements RuleInterface {
            public function id(): string { return 'TestRule'; }
            public function description(): string { return 'Test'; }
            public function check(Operation $op): array { return []; }
            public function severity(): string { return 'warning'; }
        };

        $operation = new Operation('users', 'bigIncrements', 'id', 'migrations/2024_01_01_000000_create_users_table.php', 'database/migrations', 10);
        $result = $rule->check($operation);

        $this->assertIsArray($result);
    }

    /** @test */
    public function severity_method_returns_valid_severity(): void
    {
        foreach (['error', 'warning', 'info'] as $severity) {
            $rule = new class($severity) implements RuleInterface {
                public function __construct(private string $sev) {}
                public function id(): string { return 'TestRule'; }
                public function description(): string { return 'Test'; }
                public function check(Operation $op): array { return []; }
                public function severity(): string { return $this->sev; }
            };

            $this->assertTrue(in_array($rule->severity(), ['error', 'warning', 'info']));
        }
    }
}
