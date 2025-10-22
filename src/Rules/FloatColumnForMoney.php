<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

class FloatColumnForMoney
{
    public static string $id = 'FloatColumnForMoney';

    public function description(): string
    {
        return 'Warns when float() is used for monetary columns; suggests using decimal() instead.';
    }

    /**
     * @param Operation $operation
     * @return array<Issue>
     */
    public function check(Operation $operation): array
    {
        $issues = [];

        // Check if the operation defines a float column
        if ($operation->method !== 'float') {
            return $issues;
        }

        // Try to extract column name
        $column = null;
        if (preg_match("/'([^']+)'/", $operation->args, $matches)) {
            $column = $matches[1];
        }

        // Columns likely to represent money or precision values
        $moneyLike = ['price', 'amount', 'balance', 'total', 'cost', 'revenue', 'tax', 'discount', 'fee'];

        if ($column && in_array(strtolower($column), $moneyLike)) {
            $issues[] = new Issue(
                ruleId: self::$id,
                file: $operation->file,
                message: "Column '{$column}' in table '{$operation->table}' uses float(); consider decimal(10,2) for precise monetary values.",
                severity: 'warning',
                snippet: $column
            );
        }

        return $issues;
    }
}
