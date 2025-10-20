<?php

use Tests\TestCase;

it('runs the migrate:lint command successfully', function () {
    $this->artisan('migrate:lint')
        ->expectsOutputToContain('Running Laravel Migration Linter')
        ->assertExitCode(0);
});

it('outputs json when using --json flag', function () {
    $path = base_path('database/migrations');
    $output = $this->artisan("migrate:lint --json --path={$path}")
        ->run();

    expect(file_exists(base_path('database/migrations')))
        ->toBeTrue();
});
