<?php

use Illuminate\Support\Facades\Config;
use Sufyan\MigrationLinter\Support\RuleEngine;

it('skips disabled rules from config', function () {
    // Disable AddNonNullableColumnWithoutDefault in config
    Config::set('migration-linter.rules.AddNonNullableColumnWithoutDefault.enabled', false);

    // Create a mock operation array (parser style)
    $operation = [
        'table' => 'users',
        'method' => 'string',
        'args' => "'name'",
        'file' => '2025_10_30_000001_add_name_to_users_table.php',
        'path' => '',
        'column' => 'name',
        'line' => 10,
        'rawCode' => "\$table->string('name');",
    ];

    // Run through rule engine
    $engine = new RuleEngine();
    $issues = $engine->run([$operation]);

    // Assert no issue for disabled rule
    expect(collect($issues)->pluck('rule')->all())
        ->not->toContain('AddNonNullableColumnWithoutDefault');
});
