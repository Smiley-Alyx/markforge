<?php

declare(strict_types=1);

namespace MarkForge\Tests\Renderer;

use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\ListItemNode;
use MarkForge\Nodes\ListNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\TextNode;
use MarkForge\Renderer\HtmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HtmlRenderer::class)]
final class ListRendererTest extends TestCase
{
    public function testRendersUnorderedList(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new ListNode(false, null, [
                new ListItemNode([new ParagraphNode([new TextNode('One')])]),
                new ListItemNode([new ParagraphNode([new TextNode('Two')])]),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<ul><li><p>One</p></li><li><p>Two</p></li></ul>', $html);
    }

    public function testRendersOrderedListWithStart(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new ListNode(true, 3, [
                new ListItemNode([new ParagraphNode([new TextNode('Three')])]),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<ol start="3"><li><p>Three</p></li></ol>', $html);
    }
}
