<?php

declare(strict_types=1);

namespace MarkForge\Tests\Renderer;

use MarkForge\Nodes\CodeBlockNode;
use MarkForge\Nodes\DocumentNode;
use MarkForge\Renderer\HtmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HtmlRenderer::class)]
final class CodeBlockRendererTest extends TestCase
{
    public function testRendersCodeBlockWithLanguageClass(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new CodeBlockNode("echo 1;\n", 'php'),
        ]);

        $html = $renderer->render($document);

        self::assertSame("<pre><code class=\"language-php\">echo 1;\n</code></pre>", $html);
    }

    public function testRendersCodeBlockEscapesHtml(): void
    {
        $renderer = new HtmlRenderer();
        $document = new DocumentNode([
            new CodeBlockNode('<b>x</b>', ''),
        ]);

        $html = $renderer->render($document);

        self::assertSame('<pre><code>&lt;b&gt;x&lt;/b&gt;</code></pre>', $html);
    }
}
