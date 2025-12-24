<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Sufyan\MigrationLinter\Contracts\ConfigInterface;

class ConfigInterfaceTest extends TestCase
{
    /** @test */
    public function config_interface_has_required_methods(): void
    {
        $this->assertTrue(method_exists(ConfigInterface::class, 'getRules'));
        $this->assertTrue(method_exists(ConfigInterface::class, 'isRuleEnabled'));
        $this->assertTrue(method_exists(ConfigInterface::class, 'getSeverityThreshold'));
        $this->assertTrue(method_exists(ConfigInterface::class, 'getRuleConfig'));
        $this->assertTrue(method_exists(ConfigInterface::class, 'get'));
    }

    /** @test */
    public function get_rules_returns_array(): void
    {
        $config = new class implements ConfigInterface {
            public function getRules(): array { return ['Rule1' => ['enabled' => true]]; }
            public function isRuleEnabled(string $ruleId): bool { return true; }
            public function getSeverityThreshold(): string { return 'warning'; }
            public function getRuleConfig(string $ruleId): array { return []; }
            public function get(string $key, mixed $default = null): mixed { return $default; }
        };

        $rules = $config->getRules();
        $this->assertIsArray($rules);
    }

    /** @test */
    public function is_rule_enabled_returns_boolean(): void
    {
        $config = new class implements ConfigInterface {
            public function getRules(): array { return []; }
            public function isRuleEnabled(string $ruleId): bool { return true; }
            public function getSeverityThreshold(): string { return 'warning'; }
            public function getRuleConfig(string $ruleId): array { return []; }
            public function get(string $key, mixed $default = null): mixed { return $default; }
        };

        $this->assertTrue($config->isRuleEnabled('TestRule'));
    }

    /** @test */
    public function get_severity_threshold_returns_valid_threshold(): void
    {
        $config = new class implements ConfigInterface {
            public function getRules(): array { return []; }
            public function isRuleEnabled(string $ruleId): bool { return true; }
            public function getSeverityThreshold(): string { return 'warning'; }
            public function getRuleConfig(string $ruleId): array { return []; }
            public function get(string $key, mixed $default = null): mixed { return $default; }
        };

        $threshold = $config->getSeverityThreshold();
        $this->assertTrue(in_array($threshold, ['info', 'warning', 'error']));
    }

    /** @test */
    public function get_rule_config_returns_array(): void
    {
        $config = new class implements ConfigInterface {
            public function getRules(): array { return []; }
            public function isRuleEnabled(string $ruleId): bool { return true; }
            public function getSeverityThreshold(): string { return 'warning'; }
            public function getRuleConfig(string $ruleId): array { return ['severity' => 'error']; }
            public function get(string $key, mixed $default = null): mixed { return $default; }
        };

        $ruleConfig = $config->getRuleConfig('TestRule');
        $this->assertIsArray($ruleConfig);
        $this->assertEquals('error', $ruleConfig['severity']);
    }

    /** @test */
    public function get_method_returns_value_or_default(): void
    {
        $config = new class implements ConfigInterface {
            private array $data = ['key1' => 'value1'];
            public function getRules(): array { return []; }
            public function isRuleEnabled(string $ruleId): bool { return true; }
            public function getSeverityThreshold(): string { return 'warning'; }
            public function getRuleConfig(string $ruleId): array { return []; }
            public function get(string $key, mixed $default = null): mixed {
                return $this->data[$key] ?? $default;
            }
        };

        $this->assertEquals('value1', $config->get('key1'));
        $this->assertNull($config->get('nonexistent'));
        $this->assertEquals('default', $config->get('nonexistent', 'default'));
    }
}
