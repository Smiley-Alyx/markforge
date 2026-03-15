<?php

declare(strict_types=1);

namespace MarkForge\Tests\Renderer;

use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\TableCellNode;
use MarkForge\Nodes\TableNode;
use MarkForge\Nodes\TableRowNode;
use MarkForge\Nodes\TextNode;
use MarkForge\Renderer\HtmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HtmlRenderer::class)]
final class TableRendererTest extends TestCase
{
    public function testRendersTable(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new TableNode(
                new TableRowNode([
                    new TableCellNode(true, [new TextNode('A')]),
                    new TableCellNode(true, [new TextNode('B')]),
                ]),
                [
                    new TableRowNode([
                        new TableCellNode(false, [new TextNode('1')]),
                        new TableCellNode(false, [new TextNode('2')]),
                    ]),
                ],
            ),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr></tbody></table>', $html);
    }
}
