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

it('does not generate html report unless html option is explicitly passed', function () {
    $tempDir = sys_get_temp_dir() . '/migrate-lint-' . uniqid('', true);
    mkdir($tempDir, 0755, true);

    $migrationFile = $tempDir . '/2026_01_01_000000_add_email_to_users_table.php';
    file_put_contents($migrationFile, <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email');
        });
    }
};
PHP);

    $defaultHtmlReport = storage_path('app/migration-lint-report.html');
    if (file_exists($defaultHtmlReport)) {
        unlink($defaultHtmlReport);
    }

    try {
        $lintPath = str_replace('\\', '/', $tempDir);

        $this->artisan("migrate:lint --path={$lintPath}")
            ->expectsOutputToContain('Running Laravel Migration Linter')
            ->assertExitCode(1);

        expect(file_exists($defaultHtmlReport))->toBeFalse();
    } finally {
        if (file_exists($migrationFile)) {
            unlink($migrationFile);
        }
        if (is_dir($tempDir)) {
            rmdir($tempDir);
        }
    }
});

it('uses default html path when html option is passed without a custom path', function () {
    $tempDir = sys_get_temp_dir() . '/migrate-lint-' . uniqid('', true);
    mkdir($tempDir, 0755, true);

    $migrationFile = $tempDir . '/2026_01_01_000001_add_phone_to_users_table.php';
    file_put_contents($migrationFile, <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone');
        });
    }
};
PHP);

    $defaultHtmlReport = storage_path('app/migration-lint-report.html');
    if (file_exists($defaultHtmlReport)) {
        unlink($defaultHtmlReport);
    }

    try {
        $lintPath = str_replace('\\', '/', $tempDir);

        $this->artisan("migrate:lint --path={$lintPath} --html=")
            ->expectsOutputToContain('HTML report generated')
            ->assertExitCode(1);

        expect(file_exists($defaultHtmlReport))->toBeTrue();
    } finally {
        if (file_exists($defaultHtmlReport)) {
            unlink($defaultHtmlReport);
        }
        if (file_exists($migrationFile)) {
            unlink($migrationFile);
        }
        if (is_dir($tempDir)) {
            rmdir($tempDir);
        }
    }
});
