<?php

declare(strict_types=1);

namespace MarkForge\Renderer;

use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\TextNode;

final class HtmlRenderer implements RendererInterface
{
    public function render(DocumentNode $document): string
    {
        $out = [];

        foreach ($document->children() as $block) {
            if ($block instanceof ParagraphNode) {
                $out[] = $this->renderParagraph($block);
            }
        }

        return implode("\n", $out);
    }

    private function renderParagraph(ParagraphNode $paragraph): string
    {
        $content = '';

        foreach ($paragraph->children() as $inline) {
            if ($inline instanceof TextNode) {
                $content .= $this->escape($inline->text());
            }
        }

        return '<p>' . $content . '</p>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
