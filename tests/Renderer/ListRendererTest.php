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
    public function testRendersLooseList(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new ListNode(false, null, false, [
                new ListItemNode([new ParagraphNode([new TextNode('One')])]),
                new ListItemNode([new ParagraphNode([new TextNode('Two')])]),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<ul><li><p>One</p></li><li><p>Two</p></li></ul>', $html);
    }

    public function testRendersUnorderedList(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new ListNode(false, null, true, [
                new ListItemNode([new ParagraphNode([new TextNode('One')])]),
                new ListItemNode([new ParagraphNode([new TextNode('Two')])]),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<ul><li>One</li><li>Two</li></ul>', $html);
    }

    public function testRendersOrderedListWithStart(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new ListNode(true, 3, true, [
                new ListItemNode([new ParagraphNode([new TextNode('Three')])]),
            ]),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<ol start="3"><li>Three</li></ol>', $html);
    }
}
