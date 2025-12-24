<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

class ChangeColumnTypeOnLargeTable extends AbstractRule
{
    public function id(): string
    {
        return 'ChangeColumnTypeOnLargeTable';
    }

    public function defaultSeverity(): string
    {
        return 'error';
    }

    public function description(): string
    {
        return 'Warns when changing column types on large tables, which can cause extended table locks and downtime.';
    }

    /**
     * Check for column type changes with ->change().
     *
     * @return Issue[]
     */
    public function check(Operation $operation): array
    {
        $issues = [];
        
        // Get configuration
        $config = config('migration-linter.rules.ChangeColumnTypeOnLargeTable', []);
        $checkLargeTables = $config['check_large_tables_only'] ?? true;
        $largeTableNames = config('migration-linter.large_table_names', []);
        
        $rawCode = $operation->rawCode ?? '';
        $table = $operation->table;
        $method = strtolower($operation->method ?? '');
        
        // ---------------------------------------------------------------------
        // 1️⃣ Detect ->change() modifier
        // ---------------------------------------------------------------------
        if (!str_contains(strtolower($rawCode), '->change()')) {
            return [];
        }
        
        // ---------------------------------------------------------------------
        // 2️⃣ Check if this is a column type method
        // ---------------------------------------------------------------------
        $typeChangeMethods = [
            'string', 'char', 'varchar',
            'text', 'mediumtext', 'longtext',
            'integer', 'tinyinteger', 'smallinteger', 'mediuminteger', 'biginteger',
            'unsignedinteger', 'unsignedtinyinteger', 'unsignedsmallinteger', 
            'unsignedmediuminteger', 'unsignedbiginteger',
            'float', 'double', 'decimal', 'unsigneddecimal',
            'boolean', 'enum', 'json', 'jsonb',
            'date', 'datetime', 'datetimetz', 'time', 'timetz', 'timestamp', 'timestamptz',
            'year', 'binary', 'uuid', 'ipaddress', 'macaddress',
        ];
        
        if (!in_array($method, $typeChangeMethods, true)) {
            return [];
        }
        
        // ---------------------------------------------------------------------
        // 3️⃣ Check if this is a large table (if config requires it)
        // ---------------------------------------------------------------------
        $isLargeTable = in_array($table, $largeTableNames, true);
        
        if ($checkLargeTables && !$isLargeTable) {
            // Skip check for small tables if configured
            return [];
        }
        
        // ---------------------------------------------------------------------
        // 4️⃣ Allow opt-out via safe comment
        // ---------------------------------------------------------------------
        $rawCodeLower = strtolower($rawCode);
        $safePatterns = [
            '// safe change',
            '// safechange',
            '//safe change',
            '//safechange',
            '/* safe-change',
            '/* safe change',
            '// zero-downtime',
            '//zero-downtime',
            '// verified',
            '// safe:',
            '// maintenance window',
        ];
        
        foreach ($safePatterns as $pattern) {
            if (str_contains($rawCodeLower, $pattern)) {
                return [];
            }
        }
        
        // ---------------------------------------------------------------------
        // 5️⃣ Extract column name and new type information
        // ---------------------------------------------------------------------
        $column = $operation->column ?? 'unknown';
        
        // Try to extract length/precision from args
        $typeInfo = $this->extractTypeInfo($method, $operation->args ?? '');
        
        // ---------------------------------------------------------------------
        // 6️⃣ Generate warning with actionable suggestion
        // ---------------------------------------------------------------------
        $message = sprintf(
            "Changing column type of '%s' to %s on table '%s' requires ALTER TABLE, which locks the entire table and can cause downtime on large datasets.",
            $column,
            $typeInfo,
            $table
        );
        
        // Build comprehensive suggestion
        $suggestion = $this->buildSuggestion($table, $column, $method, $typeInfo);
        
        $docsUrl = 'https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/docs/rules#-changecolumntypeonlargetable';
        
        $issues[] = $this->warn(
            $operation,
            $message,
            $column,
            $suggestion,
            $docsUrl
        );
        
        return $issues;
    }
    
    /**
     * Extract type information from method and args.
     */
    protected function extractTypeInfo(string $method, string $args): string
    {
        // Extract length/precision if present
        if (preg_match('/(\d+)/', $args, $matches)) {
            $length = $matches[1];
            
            // Check for decimal precision
            if (preg_match('/(\d+)\s*,\s*(\d+)/', $args, $decimalMatches)) {
                return "{$method}({$decimalMatches[1]}, {$decimalMatches[2]})";
            }
            
            return "{$method}({$length})";
        }
        
        return $method . '()';
    }
    
    /**
     * Build a detailed suggestion for safer column type changes.
     */
    protected function buildSuggestion(string $table, string $column, string $method, string $typeInfo): string
    {
        $suggestion = "Changing column types on large tables can take minutes or hours. Consider these approaches:\n\n";
        
        $suggestion .= "Option 1 - Zero-downtime approach (Recommended):\n";
        $suggestion .= "  1. Add new column with desired type:\n";
        $suggestion .= "     Schema::table('{$table}', function (Blueprint \$table) {\n";
        $suggestion .= "         \$table->{$method}('{$column}_new')->nullable();\n";
        $suggestion .= "     });\n\n";
        
        $suggestion .= "  2. Backfill data in batches:\n";
        $suggestion .= "     DB::table('{$table}')\n";
        $suggestion .= "         ->whereNull('{$column}_new')\n";
        $suggestion .= "         ->chunkById(1000, function (\$records) {\n";
        $suggestion .= "             foreach (\$records as \$record) {\n";
        $suggestion .= "                 DB::table('{$table}')\n";
        $suggestion .= "                     ->where('id', \$record->id)\n";
        $suggestion .= "                     ->update(['{$column}_new' => \$record->{$column}]);\n";
        $suggestion .= "             }\n";
        $suggestion .= "         });\n\n";
        
        $suggestion .= "  3. Update application code to use new column\n\n";
        
        $suggestion .= "  4. Drop old column (after verification):\n";
        $suggestion .= "     Schema::table('{$table}', function (Blueprint \$table) {\n";
        $suggestion .= "         \$table->dropColumn('{$column}'); // safe drop\n";
        $suggestion .= "     });\n\n";
        
        $suggestion .= "Option 2 - Maintenance window approach:\n";
        $suggestion .= "  • Schedule during low-traffic period\n";
        $suggestion .= "  • Put application in maintenance mode\n";
        $suggestion .= "  • Run migration with timeout buffer\n";
        $suggestion .= "  • Monitor query execution time\n\n";
        
        $suggestion .= "Option 3 - Use raw SQL with pt-online-schema-change:\n";
        $suggestion .= "  • Use Percona Toolkit for MySQL\n";
        $suggestion .= "  • Performs changes without locking table\n";
        $suggestion .= "  • Example: pt-online-schema-change --alter=\"MODIFY {$column} ...\" D={$table}\n\n";
        
        $suggestion .= "To bypass this warning (if table is small or during maintenance):\n";
        $suggestion .= "  Add '// safe change' or '// maintenance window' comment to the line.";
        
        return $suggestion;
    }
}
