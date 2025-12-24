<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Sufyan\MigrationLinter\Contracts\RuleEngineInterface;
use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

class RuleEngineInterfaceTest extends TestCase
{
    /** @test */
    public function rule_engine_interface_has_required_methods(): void
    {
        $this->assertTrue(method_exists(RuleEngineInterface::class, 'run'));
        $this->assertTrue(method_exists(RuleEngineInterface::class, 'getRules'));
        $this->assertTrue(method_exists(RuleEngineInterface::class, 'isRuleEnabled'));
    }

    /** @test */
    public function run_method_accepts_operations_array(): void
    {
        $engine = new class implements RuleEngineInterface {
            public function run(array $operations): array { return []; }
            public function getRules(): array { return []; }
            public function isRuleEnabled(string $ruleId): bool { return true; }
        };

        $operations = [
            new Operation('users', 'bigIncrements', 'id', 'test.php', 'migrations', 10),
        ];
        
        $result = $engine->run($operations);
        $this->assertIsArray($result);
    }

    /** @test */
    public function get_rules_returns_array(): void
    {
        $engine = new class implements RuleEngineInterface {
            public function run(array $operations): array { return []; }
            public function getRules(): array { return ['TestRule' => new class {}]; }
            public function isRuleEnabled(string $ruleId): bool { return true; }
        };

        $rules = $engine->getRules();
        $this->assertIsArray($rules);
    }

    /** @test */
    public function is_rule_enabled_returns_boolean(): void
    {
        $engine = new class implements RuleEngineInterface {
            public function run(array $operations): array { return []; }
            public function getRules(): array { return []; }
            public function isRuleEnabled(string $ruleId): bool { return true; }
        };

        $result = $engine->isRuleEnabled('TestRule');
        $this->assertIsBool($result);
    }
}
