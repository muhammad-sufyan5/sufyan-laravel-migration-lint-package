<?php

use Sufyan\MigrationLinter\Rules\FloatColumnForMoney;
use Sufyan\MigrationLinter\Support\Operation;

/**
 * This suite tests all behaviors of the FloatColumnForMoney rule.
 */
describe('FloatColumnForMoney Rule', function () {

    it('warns when float() is used for a money-like column', function () {
        $rule = new FloatColumnForMoney();

        $operation = new Operation(
            'orders',
            'float',
            "'price'",
            '2025_10_30_000001_add_price_to_orders.php',
            'price',
            0,
            "\$table->float('price');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("decimal(10,2)");
    });

    it('warns when double() is used for a money-like column', function () {
        $rule = new FloatColumnForMoney();

        $operation = new Operation(
            'payments',
            'double',
            "'amount'",
            '2025_10_30_000002_add_amount_to_payments.php',
            'amount',
            0,
            "\$table->double('amount');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("decimal(10,2)");
    });

    it('warns when real() is used for a money-like column', function () {
        $rule = new FloatColumnForMoney();

        $operation = new Operation(
            'wallets',
            'real',
            "'balance'",
            '2025_10_30_000003_add_balance_to_wallets.php',
            'balance',
            0,
            "\$table->real('balance');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("decimal(10,2)");
    });

    it('skips when float() is used for a non-money-like column', function () {
        $rule = new FloatColumnForMoney();

        $operation = new Operation(
            'stats',
            'float',
            "'ratio'",
            '2025_10_30_000004_add_ratio_to_stats.php',
            'ratio',
            0,
            "\$table->float('ratio');"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('skips when double() is used for a non-money-like column', function () {
        $rule = new FloatColumnForMoney();

        $operation = new Operation(
            'scores',
            'double',
            "'accuracy'",
            '2025_10_30_000005_add_accuracy_to_scores.php',
            'accuracy',
            0,
            "\$table->double('accuracy');"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('skips when check_double=false in config', function () {
        config()->set('migration-linter.rules.FloatColumnForMoney.check_double', false);

        $rule = new FloatColumnForMoney();

        $operation = new Operation(
            'payments',
            'double',
            "'amount'",
            '2025_10_30_000006_disable_double_check.php',
            'amount',
            0,
            "\$table->double('amount');"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('skips when check_real=false in config', function () {
        config()->set('migration-linter.rules.FloatColumnForMoney.check_real', false);

        $rule = new FloatColumnForMoney();

        $operation = new Operation(
            'wallets',
            'real',
            "'tax'",
            '2025_10_30_000007_disable_real_check.php',
            'tax',
            0,
            "\$table->real('tax');"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });
});
