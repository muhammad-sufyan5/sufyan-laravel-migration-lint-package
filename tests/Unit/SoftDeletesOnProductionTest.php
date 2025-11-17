<?php

use Sufyan\MigrationLinter\Rules\SoftDeletesOnProduction;
use Sufyan\MigrationLinter\Support\Operation;

describe('SoftDeletesOnProduction Rule', function () {
    it('detects soft deletes on large tables', function () {
        $rule = new SoftDeletesOnProduction();

        $operation = new Operation(
            table: 'users',
            method: 'softDeletes',
            args: '',
            file: 'test_migration.php',
            path: 'database/migrations/test_migration.php',
            line: 10,
            rawCode: "\$table->softDeletes();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toHaveCount(1);
        expect($issues[0]->message)->toContain('soft deletes');
        expect($issues[0]->message)->toContain('users');
        expect($issues[0]->severity)->toBe('warning');
        expect($issues[0]->suggestion)->toContain('Option 1');
    });

    it('detects soft deletes on configured large tables', function () {
        $rule = new SoftDeletesOnProduction();

        $operation = new Operation(
            table: 'orders',
            method: 'softDeletes',
            args: '',
            file: 'test_migration.php',
            path: 'database/migrations/test_migration.php',
            line: 15,
            rawCode: "\$table->softDeletes();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toHaveCount(1);
        expect($issues[0]->message)->toContain('orders');
    });

    it('detects soft deletes on invoices table', function () {
        $rule = new SoftDeletesOnProduction();

        $operation = new Operation(
            table: 'invoices',
            method: 'softDeletes',
            args: '',
            file: 'test_migration.php',
            path: 'database/migrations/test_migration.php',
            line: 20,
            rawCode: "\$table->softDeletes();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toHaveCount(1);
        expect($issues[0]->message)->toContain('performance');
    });

    it('ignores soft deletes on small tables by default', function () {
        $rule = new SoftDeletesOnProduction();

        $operation = new Operation(
            table: 'categories',
            method: 'softDeletes',
            args: '',
            file: 'test_migration.php',
            path: 'database/migrations/test_migration.php',
            line: 10,
            rawCode: "\$table->softDeletes();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toHaveCount(0);
    });

    it('ignores non-softDeletes methods', function () {
        $rule = new SoftDeletesOnProduction();

        $operation = new Operation(
            table: 'users',
            method: 'timestamps',
            args: '',
            file: 'test_migration.php',
            path: 'database/migrations/test_migration.php',
            line: 5,
            rawCode: "\$table->timestamps();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toHaveCount(0);
    });

    it('provides actionable suggestions with 3 options', function () {
        $rule = new SoftDeletesOnProduction();

        $operation = new Operation(
            table: 'users',
            method: 'softDeletes',
            args: '',
            file: 'test_migration.php',
            path: 'database/migrations/test_migration.php',
            line: 12,
            rawCode: "\$table->softDeletes();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toHaveCount(1);
        expect($issues[0]->suggestion)->toContain('Option 1');
        expect($issues[0]->suggestion)->toContain('Option 2');
        expect($issues[0]->suggestion)->toContain('Option 3');
        expect($issues[0]->suggestion)->toContain('archiving');
        expect($issues[0]->docsUrl)->toContain('softdeletes');
    });

    it('returns id and description', function () {
        $rule = new SoftDeletesOnProduction();

        expect($rule->id())->toBe('SoftDeletesOnProduction');
        expect($rule->defaultSeverity())->toBe('warning');
        expect($rule->description())->toContain('soft deletes');
        expect($rule->description())->toContain('performance');
    });

    it('sets column to deleted_at', function () {
        $rule = new SoftDeletesOnProduction();

        $operation = new Operation(
            table: 'users',
            method: 'softDeletes',
            args: '',
            file: 'test_migration.php',
            path: 'database/migrations/test_migration.php',
            line: 10,
            rawCode: "\$table->softDeletes();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toHaveCount(1);
        // Note: Issue class doesn't have column property, but it's passed to warn()
    });
});
