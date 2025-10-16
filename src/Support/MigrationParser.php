<?php

namespace Sufyan\MigrationLinter\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrationParser
{
    /**
     * Parse all migration files in the given path.
     *
     * @param  string  $path
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $path): array
    {
        $files = File::isFile($path)
            ? [$path]
            : File::allFiles($path);

        $operations = [];

        foreach ($files as $file) {
            $content = File::get($file->getPathname());
            $fileOperations = $this->parseFile($content, $file->getFilename(), $file->getPathname());
            $operations = array_merge($operations, $fileOperations);
        }

        return $operations;
    }

    /**
     * Parse a single migration file and extract schema operations.
     *
     * @param  string  $content
     * @param  string  $filename
     * @param  string  $path
     * @return array<int, array<string, mixed>>
     */
    protected function parseFile(string $content, string $filename, string $path): array
    {
        $operations = [];

        // Match "Schema::create('table_name'" and "Schema::table('table_name'"
        preg_match_all('/Schema::(create|table)\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*function\s*\(.*?\)\s*\{(.*?)\}\s*\);/s', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $schemaMethod = $match[1]; // create or table
            $table = $match[2];        // table name
            $body = $match[3];         // closure content

            // Now find table operations ($table->...)
            preg_match_all('/\$table->([a-zA-Z0-9_]+)\((.*?)\)/', $body, $ops, PREG_SET_ORDER);

            foreach ($ops as $op) {
                $method = $op[1];
                $args = trim($op[2]);

                $operations[] = [
                    'file' => $filename,
                    'path' => $path,
                    'table' => $table,
                    'schema_method' => $schemaMethod,
                    'method' => $method,
                    'args' => $args,
                ];
            }
        }

        return $operations;
    }
}
