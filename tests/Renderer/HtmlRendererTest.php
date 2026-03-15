<?php

declare(strict_types=1);

namespace MarkForge\Tests\Renderer;

use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\EmphasisNode;
use MarkForge\Nodes\HeadingNode;
use MarkForge\Nodes\HorizontalRuleNode;
use MarkForge\Nodes\ImageNode;
use MarkForge\Nodes\InlineCodeNode;
use MarkForge\Nodes\LinkNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\TextNode;
use MarkForge\Renderer\HtmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HtmlRenderer::class)]
final class HtmlRendererTest extends TestCase
{
    public function testRendersImage(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new ParagraphNode([
                new TextNode('Logo: '),
                new ImageNode('https://example.com/logo.png', 'Logo'),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<p>Logo: <img src="https://example.com/logo.png" alt="Logo" /></p>', $html);
    }

    public function testRendersHorizontalRule(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new HorizontalRuleNode(),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<hr />', $html);
    }

    public function testRendersInlineCode(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new ParagraphNode([
                new TextNode('Use '),
                new InlineCodeNode('php -v'),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<p>Use <code>php -v</code></p>', $html);
    }

    public function testRendersLink(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new ParagraphNode([
                new LinkNode('https://example.com', [new TextNode('Example')]),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<p><a href="https://example.com">Example</a></p>', $html);
    }

    public function testRendersEmphasis(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new ParagraphNode([
                new TextNode('Hello '),
                new EmphasisNode(1, [new TextNode('it')]),
                new TextNode(' and '),
                new EmphasisNode(2, [new TextNode('bold')]),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<p>Hello <em>it</em> and <strong>bold</strong></p>', $html);
    }

    public function testRendersHeading(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new HeadingNode(2, [
                new TextNode('Title'),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<h2>Title</h2>', $html);
    }

    public function testRendersParagraphWithEscapedText(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new ParagraphNode([
                new TextNode('Hello <world>'),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<p>Hello &lt;world&gt;</p>', $html);
    }
}
