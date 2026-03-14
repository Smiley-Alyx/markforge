<?php

declare(strict_types=1);

namespace MarkForge\Renderer;

use MarkForge\Nodes\BlockquoteNode;
use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\EmphasisNode;
use MarkForge\Nodes\HeadingNode;
use MarkForge\Nodes\HorizontalRuleNode;
use MarkForge\Nodes\InlineCodeNode;
use MarkForge\Nodes\LinkNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\TextNode;

final class HtmlRenderer implements RendererInterface
{
    public function render(DocumentNode $document): string
    {
        $out = [];

        foreach ($document->children() as $block) {
            if ($block instanceof HeadingNode) {
                $out[] = $this->renderHeading($block);
                continue;
            }

            if ($block instanceof HorizontalRuleNode) {
                $out[] = '<hr />';
                continue;
            }

            if ($block instanceof BlockquoteNode) {
                $out[] = $this->renderBlockquote($block);
                continue;
            }

            if ($block instanceof ParagraphNode) {
                $out[] = $this->renderParagraph($block);
            }
        }

        return implode("\n", $out);
    }

    private function renderHeading(HeadingNode $heading): string
    {
        $level = min(6, max(1, $heading->level()));

        $content = $this->renderInlines($heading->children());

        return '<h' . $level . '>' . $content . '</h' . $level . '>';
    }

    private function renderParagraph(ParagraphNode $paragraph): string
    {
        $content = $this->renderInlines($paragraph->children());

        return '<p>' . $content . '</p>';
    }

    private function renderBlockquote(BlockquoteNode $blockquote): string
    {
        $inner = $this->render(new DocumentNode($blockquote->children()));

        return '<blockquote>' . $inner . '</blockquote>';
    }

    /**
     * @param list<\MarkForge\Nodes\Node> $inlines
     */
    private function renderInlines(array $inlines): string
    {
        $content = '';

        foreach ($inlines as $inline) {
            if ($inline instanceof TextNode) {
                $content .= $this->escape($inline->text());
                continue;
            }

            if ($inline instanceof InlineCodeNode) {
                $content .= '<code>' . $this->escape($inline->code()) . '</code>';
                continue;
            }

            if ($inline instanceof LinkNode) {
                $href = $this->escapeAttribute($inline->url());
                $content .= '<a href="' . $href . '">' . $this->renderInlines($inline->children()) . '</a>';
                continue;
            }

            if ($inline instanceof EmphasisNode) {
                $tag = $inline->level() === 2 ? 'strong' : 'em';
                $content .= '<' . $tag . '>' . $this->renderInlines($inline->children()) . '</' . $tag . '>';
            }
        }

        return $content;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function escapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
