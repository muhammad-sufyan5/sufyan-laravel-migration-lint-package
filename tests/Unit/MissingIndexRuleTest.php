<?php

use Sufyan\MigrationLinter\Rules\MissingIndexOnForeignKey;
use Sufyan\MigrationLinter\Support\Operation;

it('detects missing index on foreign key column', function () {
    $rule = new MissingIndexOnForeignKey();
    $operation = new Operation([
        'table' => 'orders',
        'method' => 'unsignedBigInteger',
        'column' => 'user_id',
    ]);

    $issues = $rule->check($operation);

    expect($issues)->not->toBeEmpty()
        ->and($issues[0]['rule'])->toBe('MissingIndexOnForeignKey');
});
