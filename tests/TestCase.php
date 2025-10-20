<?php

namespace Sufyan\MigrationLinter\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Sufyan\MigrationLinter\MigrationLinterServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            MigrationLinterServiceProvider::class,
        ];
    }
}
