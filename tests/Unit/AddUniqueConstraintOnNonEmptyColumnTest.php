<?php

use Sufyan\MigrationLinter\Rules\AddUniqueConstraintOnNonEmptyColumn;
use Sufyan\MigrationLinter\Support\Operation;

/**
 * This suite tests all behaviors of the AddUniqueConstraintOnNonEmptyColumn rule.
 */
describe('AddUniqueConstraintOnNonEmptyColumn Rule', function () {

    it('warns when a single-column unique constraint is added explicitly', function () {
        $rule = new AddUniqueConstraintOnNonEmptyColumn();

        $operation = new Operation(
            'users',
            'unique',
            "'email'",
            '2025_10_29_000001_add_unique_email_to_users_table.php',
            'email',
            0,
            "\$table->unique('email');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("unique constraint to 'email'");
    });

    it('warns when a composite unique constraint is added', function () {
        $rule = new AddUniqueConstraintOnNonEmptyColumn();

        $operation = new Operation(
            'memberships',
            'unique',
            "['email', 'tenant_id']",
            '2025_10_29_000002_add_composite_unique.php',
            'email',
            0,
            "\$table->unique(['email', 'tenant_id']);"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("composite unique constraint");
    });

    it('warns when unique is added inline on column definition', function () {
        $rule = new AddUniqueConstraintOnNonEmptyColumn();

        $operation = new Operation(
            'users',
            'string',
            "'email'",
            '2025_10_29_000004_add_inline_unique_email.php',
            'email',
            0,
            "\$table->string('email')->unique();"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("inline on 'email'");
    });

    it('warns when inline unique is combined with change()', function () {
        $rule = new AddUniqueConstraintOnNonEmptyColumn();

        $operation = new Operation(
            'orders',
            'string',
            "'order_ref'",
            '2025_10_29_000005_alter_orders_add_unique.php',
            'order_ref',
            0,
            "\$table->string('order_ref')->unique()->change();"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("modify an existing column");
    });

    it('skips when there is no unique constraint in operation', function () {
        $rule = new AddUniqueConstraintOnNonEmptyColumn();

        $operation = new Operation(
            'products',
            'string',
            "'title'",
            '2025_10_29_000006_add_title_column.php',
            'title',
            0,
            "\$table->string('title');"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });
});
