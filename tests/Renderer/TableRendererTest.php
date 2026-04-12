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
                    new TableCellNode(true, null, [new TextNode('A')]),
                    new TableCellNode(true, null, [new TextNode('B')]),
                ]),
                [
                    new TableRowNode([
                        new TableCellNode(false, null, [new TextNode('1')]),
                        new TableCellNode(false, null, [new TextNode('2')]),
                    ]),
                ],
            ),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr></tbody></table>', $html);
    }

    public function testRendersTableAlignment(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new TableNode(
                new TableRowNode([
                    new TableCellNode(true, 'left', [new TextNode('A')]),
                    new TableCellNode(true, 'right', [new TextNode('B')]),
                    new TableCellNode(true, 'center', [new TextNode('C')]),
                ]),
                [
                    new TableRowNode([
                        new TableCellNode(false, 'left', [new TextNode('1')]),
                        new TableCellNode(false, 'right', [new TextNode('2')]),
                        new TableCellNode(false, 'center', [new TextNode('3')]),
                    ]),
                ],
            ),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<table><thead><tr><th style="text-align: left">A</th><th style="text-align: right">B</th><th style="text-align: center">C</th></tr></thead><tbody><tr><td style="text-align: left">1</td><td style="text-align: right">2</td><td style="text-align: center">3</td></tr></tbody></table>', $html);
    }
}
