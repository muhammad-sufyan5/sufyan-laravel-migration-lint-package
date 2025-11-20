<?php

namespace Sufyan\MigrationLinter\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Sufyan\MigrationLinter\Contracts\ParserInterface;
use Sufyan\MigrationLinter\Support\Operation;

class ParserInterfaceTest extends TestCase
{
    /** @test */
    public function parser_interface_has_parse_method(): void
    {
        $this->assertTrue(method_exists(ParserInterface::class, 'parse'));
    }

    /** @test */
    public function parse_method_accepts_string_path(): void
    {
        $parser = new class implements ParserInterface {
            public function parse(string $path): array {
                return [];
            }
        };

        $result = $parser->parse('/some/path');
        $this->assertIsArray($result);
    }

    /** @test */
    public function parse_method_returns_array_of_operations(): void
    {
        $parser = new class implements ParserInterface {
            public function parse(string $path): array {
                return [
                    new Operation('users', 'bigIncrements', 'id', 'test.php', 'migrations', 10),
                ];
            }
        };

        $result = $parser->parse('/path');
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Operation::class, $result[0]);
    }
}
