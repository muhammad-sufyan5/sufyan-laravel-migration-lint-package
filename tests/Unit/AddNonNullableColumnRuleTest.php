<?php

use Sufyan\MigrationLinter\Rules\AddNonNullableColumnWithoutDefault;
use Sufyan\MigrationLinter\Support\Operation;

it('detects non-nullable column without default', function () {
    $rule = new AddNonNullableColumnWithoutDefault();
    $operation = new Operation([
        'table' => 'users',
        'method' => 'string',
        'column' => 'email',
        'args' => "'email'",
    ]);

    $issues = $rule->check($operation);

    expect($issues)->not->toBeEmpty()
        ->and($issues[0]['rule'])->toBe('AddNonNullableColumnWithoutDefault');
});
