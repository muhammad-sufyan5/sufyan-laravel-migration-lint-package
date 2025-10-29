<?php

use Sufyan\MigrationLinter\Rules\DropColumnWithoutBackup;
use Sufyan\MigrationLinter\Support\Operation;

/**
 * This suite tests all behaviors of the DropColumnWithoutBackup rule.
 */
describe('DropColumnWithoutBackup Rule', function () {

    it('warns when a single column is dropped', function () {
        $rule = new DropColumnWithoutBackup();

        $operation = new Operation(
            'users',
            'dropColumn',
            "'email'",
            '2025_10_29_000001_drop_email_from_users_table.php',
            'email',
            0,
            "\$table->dropColumn('email');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("Dropping column 'email'");
    });

    it('warns when multiple columns are dropped', function () {
        $rule = new DropColumnWithoutBackup();

        $operation = new Operation(
            'orders',
            'dropColumn',
            "['amount', 'tax']",
            '2025_10_29_000002_drop_multiple_columns.php',
            'amount',
            0,
            "\$table->dropColumn(['amount', 'tax']);"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("Dropping multiple columns ('amount', 'tax')");
    });

    it('skips when marked as safe drop via comment', function () {
        $rule = new DropColumnWithoutBackup();

        $operation = new Operation(
            'customers',
            'dropColumn',
            "'phone'",
            '2025_10_29_000003_safe_drop_column.php',
            'phone',
            0,
            "\$table->dropColumn('phone'); // safe drop"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('warns even if comment exists but not safe drop', function () {
        $rule = new DropColumnWithoutBackup();

        $operation = new Operation(
            'customers',
            'dropColumn',
            "'address'",
            '2025_10_29_000004_drop_column_with_random_comment.php',
            'address',
            0,
            "\$table->dropColumn('address'); // todo: review later"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("Dropping column 'address'");
    });

    it('warns when dropColumn has no arguments', function () {
        $rule = new DropColumnWithoutBackup();

        $operation = new Operation(
            'tasks',
            'dropColumn',
            "",
            '2025_10_29_000005_drop_column_no_args.php',
            'tasks',
            0,
            "\$table->dropColumn();"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("Dropping one or more columns");
    });
});
