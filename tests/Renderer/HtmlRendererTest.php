<?php

declare(strict_types=1);

namespace MarkForge\Tests\Renderer;

use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\HeadingNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\TextNode;
use MarkForge\Renderer\HtmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HtmlRenderer::class)]
final class HtmlRendererTest extends TestCase
{
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
