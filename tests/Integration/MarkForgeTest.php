<?php

declare(strict_types=1);

namespace MarkForge\Tests\Integration;

use MarkForge\MarkForge;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MarkForge::class)]
final class MarkForgeTest extends TestCase
{
    public function testParseRunsEndToEnd(): void
    {
        $parser = new MarkForge();

        $html = $parser->parse("Hello\n\nWorld");

        self::assertSame("<p>Hello</p>\n<p>World</p>", $html);
    }
}
