<?php

declare(strict_types=1);

namespace MarkForge\Tests\Renderer;

use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\TextNode;
use MarkForge\Renderer\HtmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HtmlRenderer::class)]
final class HtmlRendererTest extends TestCase
{
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
