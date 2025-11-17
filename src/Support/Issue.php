<?php

namespace Sufyan\MigrationLinter\Support;

class Issue
{
    public function __construct(
        public string $ruleId,
        public string $severity,
        public string $message,
        public string $file,
        public int $line = 0,
        public ?string $snippet = null,
        public ?string $suggestion = null,
        public ?string $docsUrl = null
    ) {}
}
