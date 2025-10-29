<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;

class FloatColumnForMoney extends AbstractRule
{
    public function id(): string
    {
        return 'FloatColumnForMoney';
    }

    public function defaultSeverity(): string
    {
        return 'warning'; // can be overridden via config
    }

    public function description(): string
    {
        return 'Warns when float(), double(), or real() are used for monetary or precise values; suggests decimal() instead.';
    }

    /**
     * @return \Sufyan\MigrationLinter\Support\Issue[]
     */
    public function check(Operation $operation): array
    {
        $issues = [];

        $config = config('migration-linter.rules.FloatColumnForMoney', []);
        $checkDouble = $config['check_double'] ?? true;
        $checkReal   = $config['check_real'] ?? true;

        $method = strtolower($operation->method);
        $raw    = strtolower($operation->rawCode ?? '');
        $args   = strtolower($operation->args ?? '');

        // ---------------------------------------------------------------------
        // 1️⃣ Skip if not a float/double/real method (per config)
        // ---------------------------------------------------------------------
        $methods = ['float'];
        if ($checkDouble) $methods[] = 'double';
        if ($checkReal)   $methods[] = 'real';

        if (! in_array($method, $methods, true)) {
            return [];
        }

        // ---------------------------------------------------------------------
        // 2️⃣ Extract column name
        // ---------------------------------------------------------------------
        $column = null;
        if (preg_match("/'([^']+)'/", $operation->args ?? '', $m)) {
            $column = $m[1];
        }

        // ---------------------------------------------------------------------
        // 3️⃣ Determine if it looks like a money/precision field
        // ---------------------------------------------------------------------
        $moneyLikePatterns = [
            'price', 'amount', 'balance', 'total', 'cost', 'revenue',
            'tax', 'discount', 'fee', 'charge', 'credit', 'debit',
            'salary', 'bonus', 'commission'
        ];

        $isMoneyLike = $column
            && collect($moneyLikePatterns)
                ->contains(fn($p) => str_contains(strtolower($column), $p));

        // ---------------------------------------------------------------------
        // 4️⃣ Raise issue if suspicious
        // ---------------------------------------------------------------------
        if ($isMoneyLike) {
            $issues[] = $this->warn(
                $operation,
                sprintf(
                    "Column '%s' in table '%s' uses %s(); consider using decimal(10,2) or storing minor units (e.g. cents) for accurate monetary values.",
                    $column,
                    $operation->table,
                    $method
                ),
                $column
            );
        }

        return $issues;
    }
}
