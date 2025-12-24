<?php

namespace Sufyan\MigrationLinter\Rules;

use Sufyan\MigrationLinter\Support\Operation;
use Sufyan\MigrationLinter\Support\Issue;

class RenamingColumnWithoutIndex extends AbstractRule
{
    public function id(): string
    {
        return 'RenamingColumnWithoutIndex';
    }

    public function defaultSeverity(): string
    {
        return 'warning';
    }

    public function description(): string
    {
        return 'Warns when renaming columns, which can cause table locks and downtime on large tables.';
    }

    /**
     * Check for column renaming operations.
     *
     * @return Issue[]
     */
    public function check(Operation $operation): array
    {
        $issues = [];
        
        // Get configuration
        $config = config('migration-linter.rules.RenamingColumnWithoutIndex', []);
        $checkLargeTables = $config['check_large_tables_only'] ?? true;
        $largeTableNames = config('migration-linter.large_table_names', []);
        
        $method = strtolower($operation->method ?? '');
        $rawCode = $operation->rawCode ?? '';
        $table = $operation->table;
        
        // ---------------------------------------------------------------------
        // 1️⃣ Detect renameColumn() method
        // ---------------------------------------------------------------------
        if ($method !== 'renamecolumn') {
            return [];
        }
        
        // ---------------------------------------------------------------------
        // 2️⃣ Extract old and new column names
        // ---------------------------------------------------------------------
        $oldColumn = null;
        $newColumn = null;
        
        // Match: $table->renameColumn('old_name', 'new_name') with various formats
        // Support single quotes, double quotes, and whitespace variations
        if (preg_match("/renameColumn\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"].*?\)/i", $rawCode, $matches)) {
            $oldColumn = trim($matches[1]);
            $newColumn = trim($matches[2]);
        }
        
        // Fallback: try to extract from args if rawCode didn't match
        if (!$oldColumn && !$newColumn && $operation->args) {
            if (preg_match("/['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]/", $operation->args, $matches)) {
                $oldColumn = trim($matches[1]);
                $newColumn = trim($matches[2]);
            }
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
        
        // Check for various safe comment patterns
        $safePatterns = [
            '// safe rename',
            '// saferename', 
            '//safe rename',
            '//saferename',
            '/* safe-rename',
            '/* safe rename',
            '// zero-downtime',
            '//zero-downtime',
            '// verified',
            '// safe:',
        ];
        
        foreach ($safePatterns as $pattern) {
            if (str_contains($rawCodeLower, $pattern)) {
                return [];
            }
        }
        
        // ---------------------------------------------------------------------
        // 5️⃣ Generate warning with actionable suggestion
        // ---------------------------------------------------------------------
        $columnInfo = $oldColumn && $newColumn 
            ? "'{$oldColumn}' to '{$newColumn}'" 
            : "a column";
        
        $message = sprintf(
            "Renaming column %s on table '%s' can cause table locks and downtime, especially on large tables.",
            $columnInfo,
            $table
        );
        
        // Build comprehensive suggestion
        $suggestion = $this->buildSuggestion($table, $oldColumn, $newColumn);
        
        $docsUrl = 'https://muhammad-sufyan5.github.io/sufyan-laravel-migration-lint-package/docs/rules#-renamingcolumnwithoutindex';
        
        $issues[] = $this->warn(
            $operation,
            $message,
            $oldColumn,
            $suggestion,
            $docsUrl
        );
        
        return $issues;
    }
    
    /**
     * Build a detailed suggestion for safer column renaming.
     */
    protected function buildSuggestion(?string $table, ?string $oldColumn, ?string $newColumn): string
    {
        if (!$oldColumn || !$newColumn) {
            return "Consider using a zero-downtime approach:\n"
                . "  1. Create new column\n"
                . "  2. Migrate data (in batches)\n"
                . "  3. Update application code\n"
                . "  4. Drop old column in separate migration";
        }
        
        $suggestion = "For zero-downtime column renaming, use this phased approach:\n\n";
        $suggestion .= "Migration 1 - Add new column:\n";
        $suggestion .= "  Schema::table('{$table}', function (Blueprint \$table) {\n";
        $suggestion .= "      \$table->string('{$newColumn}')->nullable()->after('{$oldColumn}');\n";
        $suggestion .= "  });\n\n";
        
        $suggestion .= "Migration 2 - Migrate data (after deployment):\n";
        $suggestion .= "  DB::table('{$table}')\n";
        $suggestion .= "      ->whereNull('{$newColumn}')\n";
        $suggestion .= "      ->chunkById(1000, function (\$records) {\n";
        $suggestion .= "          foreach (\$records as \$record) {\n";
        $suggestion .= "              DB::table('{$table}')\n";
        $suggestion .= "                  ->where('id', \$record->id)\n";
        $suggestion .= "                  ->update(['{$newColumn}' => \$record->{$oldColumn}]);\n";
        $suggestion .= "          }\n";
        $suggestion .= "      });\n\n";
        
        $suggestion .= "Migration 3 - Drop old column (after code updated):\n";
        $suggestion .= "  Schema::table('{$table}', function (Blueprint \$table) {\n";
        $suggestion .= "      \$table->dropColumn('{$oldColumn}'); // safe drop\n";
        $suggestion .= "  });\n\n";
        
        $suggestion .= "Alternative: Add '// safe rename' comment if you've verified the table is small or unused.";
        
        return $suggestion;
    }
}
