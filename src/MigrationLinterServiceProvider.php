<?php

namespace Sufyan\MigrationLinter;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Sufyan\MigrationLinter\Commands\LintMigrations;

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
            ->hasCommand(LintMigrations::class);      // artisan command (will add later)
    }

    /**
     * Register services before booting (optional custom bindings).
     */
    public function registeringPackage()
    {
        // Bind singletons or shared services here if needed later
        // For example, a "migration-linter.engine" binding could go here
    }
}
