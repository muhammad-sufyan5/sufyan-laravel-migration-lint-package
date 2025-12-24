<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Services;

use Sufyan\MigrationLinter\Services\SeverityResolver;
use Sufyan\MigrationLinter\Contracts\ConfigInterface;
use Sufyan\MigrationLinter\Tests\TestCase;

class SeverityResolverTest extends TestCase
{
    private SeverityResolver $resolver;
    private ConfigInterface $config;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a mock config
        $this->config = new class implements ConfigInterface {
            public function getRules(): array
            {
                return [
                    'Rule1' => ['enabled' => true, 'severity' => 'error'],
                    'Rule2' => ['enabled' => true, 'severity' => 'info'],
                    'Rule3' => ['enabled' => true],
                ];
            }

            public function isRuleEnabled(string $ruleId): bool { return true; }
            public function getSeverityThreshold(): string { return 'warning'; }
            
            public function getRuleConfig(string $ruleId): array
            {
                return $this->getRules()[$ruleId] ?? [];
            }

            public function get(string $key, mixed $default = null): mixed { return $default; }
        };

        $this->resolver = new SeverityResolver($this->config);
    }

    /** @test */
    public function resolve_returns_custom_severity_when_provided(): void
    {
        // Custom severity should override everything
        $this->assertEquals('error', $this->resolver->resolve('Rule2', 'error'));
        $this->assertEquals('info', $this->resolver->resolve('Rule1', 'info'));
        $this->assertEquals('warning', $this->resolver->resolve('UnknownRule', 'warning'));
    }

    /** @test */
    public function resolve_returns_configured_severity_when_no_custom_provided(): void
    {
        // Should use configured severity
        $this->assertEquals('error', $this->resolver->resolve('Rule1'));
        $this->assertEquals('info', $this->resolver->resolve('Rule2'));
    }

    /** @test */
    public function resolve_returns_default_warning_when_no_config_and_no_custom(): void
    {
        // Unknown rule should return default
        $this->assertEquals('warning', $this->resolver->resolve('UnknownRule'));
        
        // Rule with no severity in config should return default
        $this->assertEquals('warning', $this->resolver->resolve('Rule3'));
    }

    /** @test */
    public function resolve_follows_priority_order(): void
    {
        // Priority 1: Custom (highest)
        $this->assertEquals('info', $this->resolver->resolve('Rule1', 'info'));
        
        // Priority 2: Config (medium)
        $this->assertEquals('error', $this->resolver->resolve('Rule1', null));
        
        // Priority 3: Default (lowest)
        $this->assertEquals('warning', $this->resolver->resolve('UnknownRule', null));
    }

    /** @test */
    public function resolve_validates_severity_values(): void
    {
        // Invalid custom severity should fall back to config
        $this->assertEquals('error', $this->resolver->resolve('Rule1', 'invalid'));
        
        // Valid custom severity should be used
        $this->assertEquals('error', $this->resolver->resolve('Rule1', 'error'));
    }

    /** @test */
    public function resolve_accepts_null_custom_severity(): void
    {
        // null should use config
        $this->assertEquals('error', $this->resolver->resolve('Rule1', null));
    }

    /** @test */
    public function resolve_works_with_all_valid_severities(): void
    {
        foreach (['error', 'warning', 'info'] as $severity) {
            $result = $this->resolver->resolve('UnknownRule', $severity);
            $this->assertEquals($severity, $result);
        }
    }

    /** @test */
    public function resolve_case_sensitive_severity_validation(): void
    {
        // Invalid case (uppercase) should fall back to default (using unconfigured rule)
        $this->assertEquals('warning', $this->resolver->resolve('UnknownRule', 'ERROR'));
        $this->assertEquals('warning', $this->resolver->resolve('UnknownRule', 'Warning'));
        $this->assertEquals('warning', $this->resolver->resolve('UnknownRule', 'INFO'));

        // Valid case (lowercase) should work
        $this->assertEquals('error', $this->resolver->resolve('Rule1', 'error'));
    }

    /** @test */
    public function resolve_with_empty_config_returns_default(): void
    {
        $config = new class implements ConfigInterface {
            public function getRules(): array { return []; }
            public function isRuleEnabled(string $ruleId): bool { return false; }
            public function getSeverityThreshold(): string { return 'info'; }
            public function getRuleConfig(string $ruleId): array { return []; }
            public function get(string $key, mixed $default = null): mixed { return $default; }
        };

        $resolver = new SeverityResolver($config);
        
        $this->assertEquals('warning', $resolver->resolve('AnyRule'));
    }

    /** @test */
    public function resolve_multiple_times_returns_consistent_results(): void
    {
        $rule1 = $this->resolver->resolve('Rule1');
        $rule1Again = $this->resolver->resolve('Rule1');
        
        $this->assertEquals($rule1, $rule1Again);
    }
}
