<?php

namespace Sufyan\MigrationLinter\Contracts;

/**
 * Contract for parsing migration files.
 *
 * Implementations extract operations from migration files for analysis.
 */
interface ParserInterface
{
    /**
     * Parse migration file(s) and extract operations.
     *
     * @param string $path Path to migration file or directory
     * @return array<int, \Sufyan\MigrationLinter\Support\Operation> Array of parsed operations
     * @throws \InvalidArgumentException If path does not exist
     */
    public function parse(string $path): array;
}
