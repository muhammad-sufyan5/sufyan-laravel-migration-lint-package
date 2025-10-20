<?php

use Tests\TestCase;
use Illuminate\Support\Facades\File;

it('generates a baseline file successfully', function () {
    $baselinePath = base_path('migration-lint-baseline.json');

    if (File::exists($baselinePath)) {
        File::delete($baselinePath);
    }

    $this->artisan('migrate:lint', ['--generate-baseline' => true])
        ->expectsOutputToContain('Baseline file generated')
        ->assertExitCode(0);
    dump('Generated file at: ' . base_path('migration-linter-baseline.json'));
    expect(File::exists($baselinePath))->toBeTrue();
});
