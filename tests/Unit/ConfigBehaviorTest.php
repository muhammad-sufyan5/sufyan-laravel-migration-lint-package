<?php

use Illuminate\Support\Facades\Config;
use Sufyan\MigrationLinter\Support\RuleEngine;
use Sufyan\MigrationLinter\Support\Operation;

it('skips disabled rules from config', function () {
    Config::set('migration-linter.rules.AddNonNullableColumnWithoutDefault.enabled', false);

    $operation = new Operation([
        'table' => 'users',
        'method' => 'string',
        'column' => 'name',
    ]);

    $engine = new RuleEngine();
    $issues = $engine->run([$operation]);

    expect(collect($issues)->pluck('rule'))
        ->not->toContain('AddNonNullableColumnWithoutDefault');
});
