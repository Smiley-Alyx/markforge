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

        $html = $parser->parse("# Title\n\nHello\n\nWorld");

        self::assertSame("<h1>Title</h1>\n<p>Hello</p>\n<p>World</p>", $html);
    }
}
