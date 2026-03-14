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

        $html = $parser->parse("# Title\n\nHello *it* and **bold** `code` [Example](https://example.com)\n\n---\n\nWorld");

        self::assertSame("<h1>Title</h1>\n<p>Hello <em>it</em> and <strong>bold</strong> <code>code</code> <a href=\"https://example.com\">Example</a></p>\n<hr />\n<p>World</p>", $html);
    }
}
