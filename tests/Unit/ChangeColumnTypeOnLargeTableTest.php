<?php

use Sufyan\MigrationLinter\Rules\ChangeColumnTypeOnLargeTable;
use Sufyan\MigrationLinter\Support\Operation;

describe('ChangeColumnTypeOnLargeTable Rule', function () {
    
    beforeEach(function () {
        // Set up test configuration
        config([
            'migration-linter.large_table_names' => ['users', 'orders', 'invoices'],
            'migration-linter.rules.ChangeColumnTypeOnLargeTable' => [
                'enabled' => true,
                'severity' => 'error',
                'check_large_tables_only' => true,
            ],
        ]);
    });

    it('detects string type change with ->change() on large table', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'users',
            'string',
            "'email', 255",
            '2025_12_24_000001_change_email_type.php',
            'test/path.php',
            10,
            "\$table->string('email', 255)->change();",
            'email'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->ruleId)->toBe('ChangeColumnTypeOnLargeTable')
            ->and($issues[0]->severity)->toBe('error')
            ->and($issues[0]->message)->toContain('email')
            ->and($issues[0]->message)->toContain('string(255)')
            ->and($issues[0]->message)->toContain('locks the entire table')
            ->and($issues[0]->suggestion)->toContain('Zero-downtime approach')
            ->and($issues[0]->suggestion)->toContain('pt-online-schema-change');
    });

    it('detects integer type change on large table', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'orders',
            'bigInteger',
            "'amount'",
            '2025_12_24_000002_change_amount_type.php',
            'test/path.php',
            10,
            "\$table->bigInteger('amount')->change();",
            'amount'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain('amount')
            ->and($issues[0]->message)->toContain('biginteger()');
    });

    it('detects decimal type change with precision', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'users',
            'decimal',
            "'balance', 10, 2",
            '2025_12_24_000003_change_balance_type.php',
            'test/path.php',
            10,
            "\$table->decimal('balance', 10, 2)->change();",
            'balance'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain('decimal(10, 2)');
    });

    it('skips column changes without ->change() modifier', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'users',
            'string',
            "'email', 255",
            '2025_12_24_000004_add_email_column.php',
            'test/path.php',
            10,
            "\$table->string('email', 255);",
            'email'
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('skips non-large table when check_large_tables_only is true', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'small_table',
            'string',
            "'name', 100",
            '2025_12_24_000005_change_name_type.php',
            'test/path.php',
            10,
            "\$table->string('name', 100)->change();",
            'name'
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('detects changes on all tables when check_large_tables_only is false', function () {
        config([
            'migration-linter.rules.ChangeColumnTypeOnLargeTable.check_large_tables_only' => false,
        ]);

        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'small_table',
            'string',
            "'name', 100",
            '2025_12_24_000006_change_name_type.php',
            'test/path.php',
            10,
            "\$table->string('name', 100)->change();",
            'name'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty();
    });

    it('skips when safe change comment is present', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'users',
            'string',
            "'email', 255",
            '2025_12_24_000007_safe_change.php',
            'test/path.php',
            10,
            "\$table->string('email', 255)->change(); // safe change",
            'email'
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('skips when maintenance window comment is present', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'users',
            'text',
            "'description'",
            '2025_12_24_000008_maintenance.php',
            'test/path.php',
            10,
            "// maintenance window\n\$table->text('description')->change();",
            'description'
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('detects text column type changes', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'invoices',
            'longText',
            "'notes'",
            '2025_12_24_000009_change_notes_type.php',
            'test/path.php',
            10,
            "\$table->longText('notes')->change();",
            'notes'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain('longtext()');
    });

    it('detects datetime column type changes', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'users',
            'timestamp',
            "'created_at'",
            '2025_12_24_000010_change_timestamp.php',
            'test/path.php',
            10,
            "\$table->timestamp('created_at')->change();",
            'created_at'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain('timestamp()');
    });

    it('detects boolean column type changes', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'orders',
            'boolean',
            "'is_paid'",
            '2025_12_24_000011_change_boolean.php',
            'test/path.php',
            10,
            "\$table->boolean('is_paid')->change();",
            'is_paid'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty();
    });

    it('detects enum column type changes', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'users',
            'enum',
            "'status', ['active', 'inactive']",
            '2025_12_24_000012_change_enum.php',
            'test/path.php',
            10,
            "\$table->enum('status', ['active', 'inactive'])->change();",
            'status'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty();
    });

    it('provides comprehensive suggestion with 3 options', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operation = new Operation(
            'users',
            'string',
            "'email', 255",
            '2025_12_24_000013_comprehensive_suggestion.php',
            'test/path.php',
            10,
            "\$table->string('email', 255)->change();",
            'email'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->suggestion)->toContain('Option 1 - Zero-downtime approach')
            ->and($issues[0]->suggestion)->toContain('Option 2 - Maintenance window approach')
            ->and($issues[0]->suggestion)->toContain('Option 3 - Use raw SQL with pt-online-schema-change')
            ->and($issues[0]->suggestion)->toContain('Add new column')
            ->and($issues[0]->suggestion)->toContain('Backfill data in batches')
            ->and($issues[0]->suggestion)->toContain('chunkById(1000')
            ->and($issues[0]->suggestion)->toContain('maintenance mode');
    });

    it('ignores non-type-change methods', function () {
        $rule = new ChangeColumnTypeOnLargeTable();

        $operations = [
            new Operation('users', 'index', "'email'", 'test.php', 'test/path.php', 10, "\$table->index('email');", 'email'),
            new Operation('users', 'unique', "'email'", 'test.php', 'test/path.php', 10, "\$table->unique('email');", 'email'),
            new Operation('users', 'dropColumn', "'old'", 'test.php', 'test/path.php', 10, "\$table->dropColumn('old');", 'old'),
        ];

        foreach ($operations as $operation) {
            $issues = $rule->check($operation);
            expect($issues)->toBeEmpty();
        }
    });

    it('respects custom severity from config', function () {
        config([
            'migration-linter.rules.ChangeColumnTypeOnLargeTable.severity' => 'warning',
        ]);

        $rule = new ChangeColumnTypeOnLargeTable();
        $rule->customSeverity = 'warning';

        $operation = new Operation(
            'users',
            'string',
            "'email', 255",
            '2025_12_24_000014_custom_severity.php',
            'test/path.php',
            10,
            "\$table->string('email', 255)->change();",
            'email'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->severity)->toBe('warning');
    });

    it('checks all three large tables from config', function () {
        $rule = new ChangeColumnTypeOnLargeTable();
        $largeTables = ['users', 'orders', 'invoices'];

        foreach ($largeTables as $table) {
            $operation = new Operation(
                $table,
                'string',
                "'col', 100",
                "2025_12_24_change_{$table}.php",
                'test/path.php',
                10,
                "\$table->string('col', 100)->change();",
                'col'
            );

            $issues = $rule->check($operation);

            expect($issues)->not->toBeEmpty()
                ->and($issues[0]->message)->toContain($table);
        }
    });
});
