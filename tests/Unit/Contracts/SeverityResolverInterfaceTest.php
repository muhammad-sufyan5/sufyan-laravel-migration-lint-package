<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Sufyan\MigrationLinter\Contracts\SeverityResolverInterface;

class SeverityResolverInterfaceTest extends TestCase
{
    /** @test */
    public function severity_resolver_interface_has_resolve_method(): void
    {
        $this->assertTrue(method_exists(SeverityResolverInterface::class, 'resolve'));
    }

    /** @test */
    public function resolve_method_returns_valid_severity(): void
    {
        $resolver = new class implements SeverityResolverInterface {
            public function resolve(string $ruleId, ?string $customSeverity = null): string {
                return $customSeverity ?? 'warning';
            }
        };

        $severity = $resolver->resolve('TestRule');
        $this->assertTrue(in_array($severity, ['error', 'warning', 'info']));
    }

    /** @test */
    public function resolve_method_respects_custom_severity(): void
    {
        $resolver = new class implements SeverityResolverInterface {
            public function resolve(string $ruleId, ?string $customSeverity = null): string {
                return $customSeverity ?? 'warning';
            }
        };

        $this->assertEquals('error', $resolver->resolve('TestRule', 'error'));
        $this->assertEquals('info', $resolver->resolve('TestRule', 'info'));
        $this->assertEquals('warning', $resolver->resolve('TestRule', null));
    }

    /** @test */
    public function resolve_method_follows_priority_order(): void
    {
        $resolver = new class implements SeverityResolverInterface {
            private array $config = [
                'Rule1' => 'error',
                'Rule2' => 'info',
            ];

            public function resolve(string $ruleId, ?string $customSeverity = null): string {
                // Priority: custom > config > default
                if ($customSeverity) {
                    return $customSeverity;
                }
                if (isset($this->config[$ruleId])) {
                    return $this->config[$ruleId];
                }
                return 'warning';
            }
        };

        // Custom overrides config
        $this->assertEquals('info', $resolver->resolve('Rule1', 'info'));
        
        // Config is used if no custom
        $this->assertEquals('error', $resolver->resolve('Rule1'));
        
        // Default is used if no config or custom
        $this->assertEquals('warning', $resolver->resolve('UnknownRule'));
    }
}
