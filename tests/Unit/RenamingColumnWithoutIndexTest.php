<?php

use Sufyan\MigrationLinter\Rules\RenamingColumnWithoutIndex;
use Sufyan\MigrationLinter\Support\Operation;

describe('RenamingColumnWithoutIndex Rule', function () {
    
    beforeEach(function () {
        // Set up test configuration
        config([
            'migration-linter.large_table_names' => ['users', 'orders', 'invoices'],
            'migration-linter.rules.RenamingColumnWithoutIndex' => [
                'enabled' => true,
                'severity' => 'warning',
                'check_large_tables_only' => true,
            ],
        ]);
    });

    it('detects renameColumn on large table', function () {
        $rule = new RenamingColumnWithoutIndex();

        $operation = new Operation(
            'users',
            'renameColumn',
            "'old_name', 'new_name'",
            '2025_12_24_000001_rename_column_in_users.php',
            'old_name',
            10,
            "\$table->renameColumn('old_name', 'new_name');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->ruleId)->toBe('RenamingColumnWithoutIndex')
            ->and($issues[0]->severity)->toBe('warning')
            ->and($issues[0]->message)->toContain('old_name')
            ->and($issues[0]->message)->toContain('new_name')
            ->and($issues[0]->message)->toContain('table locks')
            ->and($issues[0]->suggestion)->toContain('zero-downtime')
            ->and($issues[0]->suggestion)->toContain('Migration 1')
            ->and($issues[0]->docsUrl)->toContain('rules#-renamingcolumnwithoutindex');
    });

    it('skips renameColumn on non-large table when check_large_tables_only is true', function () {
        $rule = new RenamingColumnWithoutIndex();

        $operation = new Operation(
            'small_table',
            'renameColumn',
            "'old_col', 'new_col'",
            '2025_12_24_000002_rename_column_in_small_table.php',
            'old_col',
            10,
            "\$table->renameColumn('old_col', 'new_col');"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('detects renameColumn on all tables when check_large_tables_only is false', function () {
        config([
            'migration-linter.rules.RenamingColumnWithoutIndex.check_large_tables_only' => false,
        ]);

        $rule = new RenamingColumnWithoutIndex();

        $operation = new Operation(
            'small_table',
            'renameColumn',
            "'old_col', 'new_col'",
            '2025_12_24_000003_rename_column_in_small_table.php',
            'old_col',
            10,
            "\$table->renameColumn('old_col', 'new_col');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain('old_col')
            ->and($issues[0]->message)->toContain('new_col');
    });

    it('skips when safe rename comment is present', function () {
        $rule = new RenamingColumnWithoutIndex();

        $operation = new Operation(
            'users',
            'renameColumn',
            "'old_name', 'new_name'",
            '2025_12_24_000004_safe_rename.php',
            'old_name',
            10,
            "\$table->renameColumn('old_name', 'new_name'); // safe rename"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('skips when safe-rename multiline comment is present', function () {
        $rule = new RenamingColumnWithoutIndex();

        $operation = new Operation(
            'users',
            'renameColumn',
            "'old_name', 'new_name'",
            '2025_12_24_000005_safe_rename_multiline.php',
            'old_name',
            10,
            "/* safe-rename: verified table is small */\n\$table->renameColumn('old_name', 'new_name');"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('skips when zero-downtime comment is present', function () {
        $rule = new RenamingColumnWithoutIndex();

        $operation = new Operation(
            'users',
            'renameColumn',
            "'old_name', 'new_name'",
            '2025_12_24_000006_zero_downtime.php',
            'old_name',
            10,
            "// zero-downtime migration completed\n\$table->renameColumn('old_name', 'new_name');"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('detects renameColumn with different quote styles', function () {
        $rule = new RenamingColumnWithoutIndex();

        $operation = new Operation(
            'orders',
            'renameColumn',
            '"status", "order_status"',
            '2025_12_24_000007_rename_with_double_quotes.php',
            'status',
            10,
            '$table->renameColumn("status", "order_status");'
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain('status')
            ->and($issues[0]->message)->toContain('order_status');
    });

    it('detects renameColumn with extra whitespace', function () {
        $rule = new RenamingColumnWithoutIndex();

        $operation = new Operation(
            'invoices',
            'renameColumn',
            "'  old_col  ' , '  new_col  '",
            '2025_12_24_000008_rename_with_spaces.php',
            'old_col',
            10,
            "\$table->renameColumn(  'old_col'  ,  'new_col'  );"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty();
    });

    it('provides comprehensive suggestion with migration steps', function () {
        $rule = new RenamingColumnWithoutIndex();

        $operation = new Operation(
            'users',
            'renameColumn',
            "'email_address', 'email'",
            '2025_12_24_000009_rename_email_column.php',
            'email_address',
            10,
            "\$table->renameColumn('email_address', 'email');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->suggestion)->toContain('Migration 1 - Add new column')
            ->and($issues[0]->suggestion)->toContain('Migration 2 - Migrate data')
            ->and($issues[0]->suggestion)->toContain('Migration 3 - Drop old column')
            ->and($issues[0]->suggestion)->toContain('chunkById(1000')
            ->and($issues[0]->suggestion)->toContain("->nullable()->after('email_address')")
            ->and($issues[0]->suggestion)->toContain("->dropColumn('email_address')");
    });

    it('ignores non-renameColumn operations', function () {
        $rule = new RenamingColumnWithoutIndex();

        $operations = [
            new Operation('users', 'string', "'email'", 'test.php', 'email', 10, "\$table->string('email');"),
            new Operation('users', 'dropColumn', "'old_col'", 'test.php', 'old_col', 10, "\$table->dropColumn('old_col');"),
            new Operation('users', 'integer', "'age'", 'test.php', 'age', 10, "\$table->integer('age');"),
        ];

        foreach ($operations as $operation) {
            $issues = $rule->check($operation);
            expect($issues)->toBeEmpty();
        }
    });

    it('handles renameColumn without extractable column names', function () {
        $rule = new RenamingColumnWithoutIndex();

        $operation = new Operation(
            'users',
            'renameColumn',
            "\$oldName, \$newName",
            '2025_12_24_000010_dynamic_rename.php',
            'unknown',
            10,
            "\$table->renameColumn(\$oldName, \$newName);"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain('a column')
            ->and($issues[0]->suggestion)->toContain('zero-downtime approach');
    });

    it('respects custom severity from config', function () {
        config([
            'migration-linter.rules.RenamingColumnWithoutIndex.severity' => 'error',
        ]);

        $rule = new RenamingColumnWithoutIndex();
        $rule->customSeverity = 'error';

        $operation = new Operation(
            'users',
            'renameColumn',
            "'old_name', 'new_name'",
            '2025_12_24_000011_rename_with_error.php',
            'old_name',
            10,
            "\$table->renameColumn('old_name', 'new_name');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->severity)->toBe('error');
    });

    it('checks all three large tables from config', function () {
        $rule = new RenamingColumnWithoutIndex();
        $largeTables = ['users', 'orders', 'invoices'];

        foreach ($largeTables as $table) {
            $operation = new Operation(
                $table,
                'renameColumn',
                "'old', 'new'",
                "2025_12_24_rename_{$table}.php",
                'old',
                10,
                "\$table->renameColumn('old', 'new');"
            );

            $issues = $rule->check($operation);

            expect($issues)->not->toBeEmpty()
                ->and($issues[0]->message)->toContain($table);
        }
    });
});
