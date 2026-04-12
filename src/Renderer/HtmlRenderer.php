<?php

declare(strict_types=1);

namespace MarkForge\Renderer;

use MarkForge\Nodes\BlockquoteNode;
use MarkForge\Nodes\CodeBlockNode;
use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\EmphasisNode;
use MarkForge\Nodes\HeadingNode;
use MarkForge\Nodes\HorizontalRuleNode;
use MarkForge\Nodes\ImageNode;
use MarkForge\Nodes\InlineCodeNode;
use MarkForge\Nodes\LinkNode;
use MarkForge\Nodes\ListItemNode;
use MarkForge\Nodes\ListNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\StrikethroughNode;
use MarkForge\Nodes\TableCellNode;
use MarkForge\Nodes\TableNode;
use MarkForge\Nodes\TableRowNode;
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

            if ($block instanceof ListNode) {
                $out[] = $this->renderList($block);
                continue;
            }

            if ($block instanceof CodeBlockNode) {
                $out[] = $this->renderCodeBlock($block);
                continue;
            }

            if ($block instanceof TableNode) {
                $out[] = $this->renderTable($block);
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

    private function renderList(ListNode $list): string
    {
        $tag = $list->ordered() ? 'ol' : 'ul';
        $attrs = '';
        if ($list->ordered() && $list->start() !== null && $list->start() !== 1) {
            $attrs = ' start="' . $this->escapeAttribute((string) $list->start()) . '"';
        }

        $itemsHtml = '';
        foreach ($list->items() as $item) {
            $itemsHtml .= $this->renderListItem($item, $list->tight());
        }

        return '<' . $tag . $attrs . '>' . $itemsHtml . '</' . $tag . '>';
    }

    private function renderListItem(ListItemNode $item, bool $tight): string
    {
        $inner = '';
        foreach ($item->children() as $child) {
            if ($child instanceof ParagraphNode) {
                if ($tight) {
                    $inner .= $this->renderInlines($child->children());
                    continue;
                }

                $inner .= $this->renderParagraph($child);
                continue;
            }

            if ($child instanceof ListNode) {
                $inner .= $this->renderList($child);
            }
        }

        return '<li>' . $inner . '</li>';
    }

    private function renderCodeBlock(CodeBlockNode $codeBlock): string
    {
        $attrs = '';
        if ($codeBlock->info() !== '') {
            $attrs = ' class="language-' . $this->escapeAttribute($codeBlock->info()) . '"';
        }

        return '<pre><code' . $attrs . '>' . $this->escape($codeBlock->code()) . '</code></pre>';
    }

    private function renderTable(TableNode $table): string
    {
        $thead = '<thead>' . $this->renderTableRow($table->header()) . '</thead>';
        $tbodyRows = '';
        foreach ($table->rows() as $row) {
            $tbodyRows .= $this->renderTableRow($row);
        }
        $tbody = '<tbody>' . $tbodyRows . '</tbody>';

        return '<table>' . $thead . $tbody . '</table>';
    }

    private function renderTableRow(TableRowNode $row): string
    {
        $cells = '';
        foreach ($row->cells() as $cell) {
            $cells .= $this->renderTableCell($cell);
        }

        return '<tr>' . $cells . '</tr>';
    }

    private function renderTableCell(TableCellNode $cell): string
    {
        $tag = $cell->header() ? 'th' : 'td';
        $content = $this->renderInlines($cell->children());

        return '<' . $tag . '>' . $content . '</' . $tag . '>';
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

            if ($inline instanceof ImageNode) {
                $content .= '<img src="' . $this->escapeAttribute($inline->src()) . '" alt="' . $this->escapeAttribute($inline->alt()) . '" />';
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
                continue;
            }

            if ($inline instanceof StrikethroughNode) {
                $content .= '<del>' . $this->renderInlines($inline->children()) . '</del>';
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
