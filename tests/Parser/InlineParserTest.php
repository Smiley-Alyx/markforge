<?php

declare(strict_types=1);

namespace MarkForge\Tests\Parser;

use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Parser\InlineParser;
use MarkForge\Renderer\HtmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InlineParser::class)]
final class InlineParserTest extends TestCase
{
    public function testBackslashEscapesPreventEmphasis(): void
    {
        $parser = new InlineParser();
        $renderer = new HtmlRenderer();

        $nodes = $parser->parse('Hello \*world\*');

        $html = $renderer->render(new DocumentNode([
            new ParagraphNode($nodes),
        ]));

        self::assertSame('<p>Hello *world*</p>', $html);
    }

    public function testAsteriskInsideWordDoesNotCreateEmphasis(): void
    {
        $parser = new InlineParser();
        $renderer = new HtmlRenderer();

        $nodes = $parser->parse('a*b');

        $html = $renderer->render(new DocumentNode([
            new ParagraphNode($nodes),
        ]));

        self::assertSame('<p>a*b</p>', $html);
    }

    public function testParsesAutolink(): void
    {
        $parser = new InlineParser();
        $renderer = new HtmlRenderer();

        $nodes = $parser->parse('<https://example.com>');

        $html = $renderer->render(new DocumentNode([
            new ParagraphNode($nodes),
        ]));

        self::assertSame('<p><a href="https://example.com">https://example.com</a></p>', $html);
    }

    public function testParsesMailtoAutolink(): void
    {
        $parser = new InlineParser();
        $renderer = new HtmlRenderer();

        $nodes = $parser->parse('<mailto:test@example.com>');

        $html = $renderer->render(new DocumentNode([
            new ParagraphNode($nodes),
        ]));

        self::assertSame('<p><a href="mailto:test@example.com">mailto:test@example.com</a></p>', $html);
    }
}
