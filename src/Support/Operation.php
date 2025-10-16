<?php

namespace Sufyan\MigrationLinter\Support;

class Operation
{
    public function __construct(
        public string $table,
        public string $method,
        public string $args,
        public string $file,
        public string $path,
        public ?int $line = null,
        public ?string $rawCode = null,
        public ?string $column = null,
    ) {}
}
