<?php

use Symfony\Component\Console\Output\BufferedOutput;
use Sufyan\MigrationLinter\Support\Reporter;

it('renders json correctly', function () {
    $issues = [
        [
            'file' => '2025_01_01_create_users_table.php',
            'rule' => 'AddNonNullableColumnWithoutDefault',
            'column' => 'email',
            'severity' => 'warning',
            'message' => 'Adding NOT NULL column without default.',
        ],
    ];

    $output = new BufferedOutput();
    $reporter = new Reporter($output);

    $json = json_encode($issues, JSON_PRETTY_PRINT);
    $reporter->render($issues, true);

    expect($output->fetch())->toContain('AddNonNullableColumnWithoutDefault');
});
