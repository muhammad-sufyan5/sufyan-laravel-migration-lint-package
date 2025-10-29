<?php

use Sufyan\MigrationLinter\Rules\MissingIndexOnForeignKey;
use Sufyan\MigrationLinter\Support\Operation;

/**
 * This suite tests all known behaviors of the MissingIndexOnForeignKey rule.
 */
describe('MissingIndexOnForeignKey Rule', function () {
    
    it('skips when _id column already has index', function () {
        $rule = new MissingIndexOnForeignKey();

        $operation = new Operation(
            'orders',
            'unsignedBigInteger',
            "'user_id'",
            '2025_10_29_000002_add_user_id_index.php',
            'user_id',
            0,
            "\$table->unsignedBigInteger('user_id')->index();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('warns when foreignId has no constrained', function () {
        $rule = new MissingIndexOnForeignKey();

        $operation = new Operation(
            'posts',
            'foreignId',
            "'user_id'",
            '2025_10_29_000003_add_foreignid_to_posts_table.php',
            'user_id',
            0,
            "\$table->foreignId('user_id');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("no ->constrained()");
    });

    it('skips when foreignId is constrained', function () {
        $rule = new MissingIndexOnForeignKey();

        $operation = new Operation(
            'posts',
            'foreignId',
            "'user_id'",
            '2025_10_29_000004_add_constrained_foreignid.php',
            'user_id',
            0,
            "\$table->foreignId('user_id')->constrained();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('warns when morphs has no index', function () {
        $rule = new MissingIndexOnForeignKey();

        $operation = new Operation(
            'comments',
            'morphs',
            "'commentable'",
            '2025_10_29_000005_add_morphs_to_comments_table.php',
            'commentable',
            0,
            "\$table->morphs('commentable');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("Polymorphic relation");
    });

    it('skips when morphs already has index', function () {
        $rule = new MissingIndexOnForeignKey();

        $operation = new Operation(
            'comments',
            'nullableMorphs',
            "'commentable'",
            '2025_10_29_000006_add_indexed_nullable_morphs.php',
            'commentable',
            0,
            "\$table->nullableMorphs('commentable')->index();"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });

    it('warns when composite foreign key has no index', function () {
        $rule = new MissingIndexOnForeignKey();

        $operation = new Operation(
            'memberships',
            'foreign',
            "['user_id','tenant_id']",
            '2025_10_29_000007_add_composite_fk.php',
            'user_id',
            0,
            "\$table->foreign(['user_id','tenant_id'])->references(['id','id'])->on('users');"
        );

        $issues = $rule->check($operation);

        expect($issues)->not->toBeEmpty()
            ->and($issues[0]->message)->toContain("Composite foreign key");
    });

    it('skips when composite foreign key has index', function () {
        $rule = new MissingIndexOnForeignKey();

        $operation = new Operation(
            'memberships',
            'foreign',
            "['user_id','tenant_id']",
            '2025_10_29_000008_add_composite_fk_with_index.php',
            'user_id',
            0,
            "\$table->foreign(['user_id','tenant_id'])->references(['id','id'])->on('users'); \$table->index(['user_id','tenant_id']);"
        );

        $issues = $rule->check($operation);

        expect($issues)->toBeEmpty();
    });
});
