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

        $html = $parser->parse("# Title\n\n| A | B |\n|---|---|\n| 1 | 2 |\n\n```php\necho 1;\n```\n\n- One\n  continuation\n  - Nested\n- Two\n\n> Quote *it*\n>\n> - Q1\n> - Q2\n>\n> | X | Y |\n> |---|---|\n> | 9 | 8 |\n>\n> ```js\n> console.log(1);\n> ```\n>\n> > Nested quote\n\nHello **bold** ~~old~~ `code` [Example](https://example.com) ![Logo](https://example.com/logo.png)\n\n---\n\nWorld");

        self::assertSame("<h1>Title</h1>\n<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr></tbody></table>\n<pre><code class=\"language-php\">echo 1;</code></pre>\n<ul><li>One\ncontinuation<ul><li>Nested</li></ul></li><li>Two</li></ul>\n<blockquote><p>Quote <em>it</em></p>\n<ul><li>Q1</li><li>Q2</li></ul>\n<table><thead><tr><th>X</th><th>Y</th></tr></thead><tbody><tr><td>9</td><td>8</td></tr></tbody></table>\n<pre><code class=\"language-js\">console.log(1);</code></pre>\n<blockquote><p>Nested quote</p></blockquote></blockquote>\n<p>Hello <strong>bold</strong> <del>old</del> <code>code</code> <a href=\"https://example.com\">Example</a> <img src=\"https://example.com/logo.png\" alt=\"Logo\" /></p>\n<hr />\n<p>World</p>", $html);
    }
}
