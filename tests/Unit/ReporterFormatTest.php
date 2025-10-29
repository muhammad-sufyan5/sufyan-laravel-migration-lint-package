<?php

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Illuminate\Console\OutputStyle;
use Sufyan\MigrationLinter\Support\Reporter;
use Sufyan\MigrationLinter\Support\Issue;

it('renders json correctly', function () {
    $issues = [
        new Issue(
            ruleId: 'AddNonNullableColumnWithoutDefault',
            severity: 'warning',
            message: 'Adding NOT NULL column without default.',
            file: '2025_01_01_create_users_table.php',
            line: 10,
        ),
    ];

    $input = new ArrayInput([]);
    $outputBuffer = new BufferedOutput();
    $output = new OutputStyle($input, $outputBuffer);

    $reporter = new Reporter($output);
    $reporter->render($issues, true); // true = JSON mode

    expect($outputBuffer->fetch())->toContain('AddNonNullableColumnWithoutDefault');
});
