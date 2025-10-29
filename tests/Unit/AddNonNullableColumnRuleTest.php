<?php

use Sufyan\MigrationLinter\Rules\AddNonNullableColumnWithoutDefault;
use Sufyan\MigrationLinter\Support\Operation;

/**
 * This suite tests all known behaviors of the AddNonNullableColumnWithoutDefault rule.
 */
describe('AddNonNullableColumnWithoutDefault Rule', function () {

    it('detects non-nullable column without default', function () {
        $rule = new AddNonNullableColumnWithoutDefault();

        $operation = new Operation(
            'users',
            'string',
            "'email'",
            '2025_10_29_000001_add_email_to_users_table.php',
            'email',
            0,
            "\$table->string('email');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->ruleId)->toBe('AddNonNullableColumnWithoutDefault')
            ->and($issues[0]->message)->toContain("Adding NOT NULL column");
    });

    it('skips when default value is present', function () {
        $rule = new AddNonNullableColumnWithoutDefault();

        $operation = new Operation(
            'users',
            'string',
            "'role'",
            '2025_10_29_000002_add_role_to_users_table.php',
            'role',
            0,
            "\$table->string('role')->default('user')->nullable(false);"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('skips when column is explicitly nullable', function () {
        $rule = new AddNonNullableColumnWithoutDefault();

        $operation = new Operation(
            'users',
            'string',
            "'nickname'",
            '2025_10_29_000003_add_nickname_to_users_table.php',
            'nickname',
            0,
            "\$table->string('nickname')->nullable();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('detects change() operation making column NOT NULL without default', function () {
        $rule = new AddNonNullableColumnWithoutDefault();

        $operation = new Operation(
            'orders',
            'string',
            "'payment_status'",
            '2025_10_29_000005_alter_orders_table.php',
            'payment_status',
            0,
            "\$table->string('payment_status')->nullable(false)->change();"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain('Changing column');
    });

    it('skips new table creation migrations (Schema::create)', function () {
        $rule = new AddNonNullableColumnWithoutDefault();

        $operation = new Operation(
            'tasks',
            'string',
            "'title'",
            '2025_07_30_070627_create_tasks_table.php',
            'title',
            0,
            "\$table->string('title')->nullable(false);"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });
});
