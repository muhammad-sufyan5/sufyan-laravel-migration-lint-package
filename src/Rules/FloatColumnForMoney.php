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
        return 'Warns when float() is used for monetary columns; suggests using decimal() instead.';
    }

    /**
     * @return \Sufyan\MigrationLinter\Support\Issue[]
     */
    public function check(Operation $operation): array
    {
        // only care about float() columns (add double/real here if you want)
        if ($operation->method !== 'float') {
            return [];
        }

        // extract column name from args like "'price'"
        $column = null;
        if (preg_match("/'([^']+)'/", $operation->args ?? '', $m)) {
            $column = $m[1];
        }

        // money-ish names
        $moneyLike = ['price', 'amount', 'balance', 'total', 'cost', 'revenue', 'tax', 'discount', 'fee'];

        if ($column && in_array(strtolower($column), $moneyLike, true)) {
            return [
                $this->warn(
                    $operation,
                    "Column '{$column}' in table '{$operation->table}' uses float(); consider decimal(10,2) for precise monetary values.",
                    $column
                ),
            ];
        }

        return [];
    }
}
