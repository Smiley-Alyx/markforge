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

        $html = $parser->parse("# Title\n\n- One\n- Two\n\n> Quote *it*\n\nHello **bold** `code` [Example](https://example.com)\n\n---\n\nWorld");

        self::assertSame("<h1>Title</h1>\n<ul><li><p>One</p></li><li><p>Two</p></li></ul>\n<blockquote><p>Quote <em>it</em></p></blockquote>\n<p>Hello <strong>bold</strong> <code>code</code> <a href=\"https://example.com\">Example</a></p>\n<hr />\n<p>World</p>", $html);
    }
}
