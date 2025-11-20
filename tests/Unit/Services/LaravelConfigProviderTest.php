<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Services;

use Sufyan\MigrationLinter\Services\LaravelConfigProvider;
use Illuminate\Config\Repository as ConfigRepository;
use Sufyan\MigrationLinter\Tests\TestCase;

class LaravelConfigProviderTest extends TestCase
{
    private LaravelConfigProvider $provider;
    private ConfigRepository $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new ConfigRepository([
            'migration-linter' => [
                'severity_threshold' => 'warning',
                'rules' => [
                    'AddNonNullableColumnWithoutDefault' => [
                        'enabled' => true,
                        'severity' => 'error',
                    ],
                    'MissingIndexOnForeignKey' => [
                        'enabled' => true,
                        'severity' => 'warning',
                    ],
                    'FloatColumnForMoney' => [
                        'enabled' => false,
                    ],
                ],
            ],
        ]);

        $this->provider = new LaravelConfigProvider($this->config);
    }

    /** @test */
    public function get_rules_returns_all_rules_configuration(): void
    {
        $rules = $this->provider->getRules();

        $this->assertIsArray($rules);
        $this->assertCount(3, $rules);
        $this->assertArrayHasKey('AddNonNullableColumnWithoutDefault', $rules);
        $this->assertArrayHasKey('MissingIndexOnForeignKey', $rules);
        $this->assertArrayHasKey('FloatColumnForMoney', $rules);
    }

    /** @test */
    public function is_rule_enabled_returns_true_for_enabled_rules(): void
    {
        $this->assertTrue($this->provider->isRuleEnabled('AddNonNullableColumnWithoutDefault'));
        $this->assertTrue($this->provider->isRuleEnabled('MissingIndexOnForeignKey'));
    }

    /** @test */
    public function is_rule_enabled_returns_false_for_disabled_rules(): void
    {
        $this->assertFalse($this->provider->isRuleEnabled('FloatColumnForMoney'));
    }

    /** @test */
    public function is_rule_enabled_returns_false_for_nonexistent_rules(): void
    {
        $this->assertFalse($this->provider->isRuleEnabled('NonexistentRule'));
    }

    /** @test */
    public function get_severity_threshold_returns_configured_threshold(): void
    {
        $threshold = $this->provider->getSeverityThreshold();

        $this->assertEquals('warning', $threshold);
        $this->assertTrue(in_array($threshold, ['info', 'warning', 'error']));
    }

    /** @test */
    public function get_severity_threshold_returns_default_when_not_configured(): void
    {
        $config = new ConfigRepository(['migration-linter' => []]);
        $provider = new LaravelConfigProvider($config);

        $this->assertEquals('warning', $provider->getSeverityThreshold());
    }

    /** @test */
    public function get_rule_config_returns_specific_rule_configuration(): void
    {
        $ruleConfig = $this->provider->getRuleConfig('AddNonNullableColumnWithoutDefault');

        $this->assertIsArray($ruleConfig);
        $this->assertTrue($ruleConfig['enabled']);
        $this->assertEquals('error', $ruleConfig['severity']);
    }

    /** @test */
    public function get_rule_config_returns_empty_array_for_nonexistent_rule(): void
    {
        $ruleConfig = $this->provider->getRuleConfig('NonexistentRule');

        $this->assertIsArray($ruleConfig);
        $this->assertEmpty($ruleConfig);
    }

    /** @test */
    public function get_method_retrieves_nested_configuration_with_dot_notation(): void
    {
        $severity = $this->provider->get('rules.MissingIndexOnForeignKey.severity');

        $this->assertEquals('warning', $severity);
    }

    /** @test */
    public function get_method_returns_default_for_nonexistent_key(): void
    {
        $value = $this->provider->get('nonexistent', 'default');

        $this->assertEquals('default', $value);
    }

    /** @test */
    public function get_method_returns_null_when_key_not_found_and_no_default(): void
    {
        $value = $this->provider->get('nonexistent');

        $this->assertNull($value);
    }

    /** @test */
    public function get_method_returns_true_value_not_default(): void
    {
        $threshold = $this->provider->get('severity_threshold', 'error');

        // Should return 'warning' not the default 'error'
        $this->assertEquals('warning', $threshold);
    }
}
