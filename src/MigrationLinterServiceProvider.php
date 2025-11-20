<?php

namespace Sufyan\MigrationLinter;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Sufyan\MigrationLinter\Commands\LintMigrations;
use Sufyan\MigrationLinter\Contracts\ConfigInterface;
use Sufyan\MigrationLinter\Contracts\SeverityResolverInterface;
use Sufyan\MigrationLinter\Contracts\ParserInterface;
use Sufyan\MigrationLinter\Contracts\RuleEngineInterface;
use Sufyan\MigrationLinter\Contracts\ReporterInterface;
use Sufyan\MigrationLinter\Contracts\FormatterInterface;
use Sufyan\MigrationLinter\Services\LaravelConfigProvider;
use Sufyan\MigrationLinter\Services\SeverityResolver;
use Sufyan\MigrationLinter\Support\MigrationParser;
use Sufyan\MigrationLinter\Support\RuleEngine;

class MigrationLinterServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package (name, config, commands, migrations, etc.)
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('migration-linter')                // package name slug
            ->hasConfigFile()                         // publishable config file
            ->hasCommand(LintMigrations::class);      // artisan command
    }

    /**
     * Register services before booting.
     *
     * Binds all SOLID contracts to their implementations for dependency injection.
     */
    public function registeringPackage(): void
    {
        // ✅ ConfigInterface → LaravelConfigProvider (singleton)
        $this->app->singleton(ConfigInterface::class, LaravelConfigProvider::class);

        // ✅ SeverityResolverInterface → SeverityResolver (singleton)
        $this->app->singleton(SeverityResolverInterface::class, SeverityResolver::class);

        // ✅ ParserInterface → MigrationParser (transient)
        $this->app->bind(ParserInterface::class, MigrationParser::class);

        // ✅ RuleEngineInterface → RuleEngine (transient, will be wired with DI)
        $this->app->bind(RuleEngineInterface::class, RuleEngine::class);
    }

    /**
     * Boot the service provider.
     *
     * Wire up automatic dependency injection for rule instances.
     */
    public function bootingPackage(): void
    {
        // ✅ After all services are registered, configure rule injection
        // When a rule is resolved via the container, inject SeverityResolverInterface
        $this->wireRuleDependencies();
    }

    /**
     * Wire SeverityResolverInterface into all AbstractRule instances.
     *
     * This ensures rules resolved from the container automatically get
     * the severity resolver injected.
     */
    protected function wireRuleDependencies(): void
    {
        // This happens in RuleEngine when it resolves rules via app($class)
        // The container will automatically inject SeverityResolverInterface
        // because AbstractRule constructor will type-hint it (or we set it explicitly)

        // For now, this is a placeholder that gets called during boot.
        // The actual wiring happens in RuleEngine->loadRules() when it calls app($class)
    }
}
