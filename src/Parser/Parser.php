<?php

declare(strict_types=1);

namespace MarkForge\Parser;

use MarkForge\Nodes\BlockquoteNode;
use MarkForge\Nodes\CodeBlockNode;
use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\HeadingNode;
use MarkForge\Nodes\ListItemNode;
use MarkForge\Nodes\ListNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\TableCellNode;
use MarkForge\Nodes\TableNode;
use MarkForge\Nodes\TableRowNode;
use MarkForge\Nodes\HorizontalRuleNode;
use MarkForge\Tokenizer\TokenType;
use MarkForge\Tokenizer\TokenStream;
use MarkForge\Tokenizer\Tokenizer;

final class Parser implements ParserInterface
{
    public function __construct(
        private readonly InlineParserInterface $inlineParser = new InlineParser(),
    ) {
    }

    public function parse(TokenStream $tokens): DocumentNode
    {
        $children = [];

        foreach ($tokens as $token) {
            if ($token->type === TokenType::Heading) {
                $level = (int) ($token->data['level'] ?? 1);
                $children[] = new HeadingNode($level, $this->parseInlines($token->value));
                continue;
            }

            if ($token->type === TokenType::HorizontalRule) {
                $children[] = new HorizontalRuleNode();
                continue;
            }

            if ($token->type === TokenType::Blockquote) {
                $innerTokenizer = new Tokenizer();
                $innerTokens = $innerTokenizer->tokenize($token->value);
                $innerDocument = $this->parse($innerTokens);
                $children[] = new BlockquoteNode($innerDocument->children());
                continue;
            }

            if ($token->type === TokenType::List) {
                $ordered = (bool) ($token->data['ordered'] ?? false);
                $start = isset($token->data['start']) ? (int) $token->data['start'] : null;

                $children[] = $this->parseListBlock($token->value, $ordered, $start);
                continue;
            }

            if ($token->type === TokenType::CodeBlock) {
                $info = (string) ($token->data['info'] ?? '');
                $children[] = new CodeBlockNode($token->value, $info);
                continue;
            }

            if ($token->type === TokenType::Table) {
                /** @var list<string> $header */
                $header = $token->data['header'] ?? [];
                /** @var list<list<string>> $rows */
                $rows = $token->data['rows'] ?? [];

                $headerCells = [];
                foreach ($header as $cellText) {
                    $headerCells[] = new TableCellNode(true, $this->parseInlines($cellText));
                }

                $bodyRows = [];
                foreach ($rows as $row) {
                    $cells = [];
                    foreach ($row as $cellText) {
                        $cells[] = new TableCellNode(false, $this->parseInlines($cellText));
                    }
                    $bodyRows[] = new TableRowNode($cells);
                }

                $children[] = new TableNode(new TableRowNode($headerCells), $bodyRows);
                continue;
            }

            if ($token->type !== TokenType::Paragraph) {
                continue;
            }

            $children[] = new ParagraphNode($this->parseInlines($token->value));
        }

        return new DocumentNode($children);
    }

    /**
     * @return list<\MarkForge\Nodes\Node>
     */
    private function parseInlines(string $text): array
    {
        return $this->inlineParser->parse($text);
    }

    private function parseListBlock(string $markdown, bool $ordered, ?int $start): ListNode
    {
        $lines = explode("\n", $markdown);

        [$items, $tight] = $this->parseListItems($lines, 0);

        return new ListNode($ordered, $start, $tight, $items);
    }

    /**
     * @param list<string> $lines
     * @return array{0: list<ListItemNode>, 1: bool}
     */
    private function parseListItems(array $lines, int $baseIndent): array
    {
        $idx = 0;
        $max = count($lines);

        $items = [];
        $loose = false;

        while ($idx < $max) {
            $line = $lines[$idx];

            if (trim($line) === '') {
                $idx++;
                continue;
            }

            $marker = $this->parseListMarker($line);
            if ($marker === null || $marker['indent'] !== $baseIndent) {
                break;
            }

            $idx++;

            $paragraphLines = [$marker['text']];
            $children = [];

            $sawBlank = false;

            while ($idx < $max) {
                $current = $lines[$idx];

                if (trim($current) === '') {
                    $sawBlank = true;
                    $idx++;
                    continue;
                }

                $nextMarker = $this->parseListMarker($current);
                if ($nextMarker !== null && $nextMarker['indent'] === $baseIndent) {
                    if ($sawBlank) {
                        $loose = true;
                    }
                    break;
                }

                if ($nextMarker !== null && $nextMarker['indent'] > $baseIndent) {
                    $nestedLines = array_slice($lines, $idx);
                    [$nestedItems, $nestedTight, $consumed] = $this->parseNestedList($nestedLines, $nextMarker['indent']);
                    $children[] = new ListNode($nextMarker['ordered'], $nextMarker['start'], $nestedTight, $nestedItems);
                    $idx += $consumed;
                    continue;
                }

                $indent = $this->countIndent($current);
                if ($indent > $baseIndent) {
                    if ($sawBlank) {
                        $loose = true;
                        $paragraphLines[] = '';
                        $sawBlank = false;
                    }

                    $paragraphLines[] = ltrim($current);
                    $idx++;
                    continue;
                }

                break;
            }

            $paragraphText = $this->normalizeParagraphLines($paragraphLines);
            $children = array_merge([
                new ParagraphNode($this->parseInlines($paragraphText)),
            ], $children);

            $items[] = new ListItemNode($children);
        }

        return [$items, !$loose];
    }

    /**
     * @param list<string> $lines
     * @return array{0: list<ListItemNode>, 1: bool, 2: int}
     */
    private function parseNestedList(array $lines, int $baseIndent): array
    {
        $consumedLines = [];
        $idx = 0;
        $max = count($lines);

        while ($idx < $max) {
            $line = $lines[$idx];
            if (trim($line) === '') {
                $consumedLines[] = $line;
                $idx++;
                continue;
            }

            $indent = $this->countIndent($line);
            if ($indent < $baseIndent) {
                break;
            }

            $consumedLines[] = $line;
            $idx++;
        }

        [$items, $tight] = $this->parseListItems($consumedLines, $baseIndent);

        $first = null;
        foreach ($consumedLines as $l) {
            if (trim($l) === '') {
                continue;
            }
            $first = $this->parseListMarker($l);
            break;
        }

        if ($first !== null) {
            return [$items, $tight, $idx];
        }

        return [[], true, $idx];
    }

    /**
     * @return array{indent: int, ordered: bool, start: ?int, text: string}|null
     */
    private function parseListMarker(string $line): ?array
    {
        if (preg_match('/^(\s*)([-*+])\s+(.*)$/', $line, $m) === 1) {
            return [
                'indent' => strlen($m[1]),
                'ordered' => false,
                'start' => null,
                'text' => $m[3],
            ];
        }

        if (preg_match('/^(\s*)(\d+)\.\s+(.*)$/', $line, $m) === 1) {
            return [
                'indent' => strlen($m[1]),
                'ordered' => true,
                'start' => (int) $m[2],
                'text' => $m[3],
            ];
        }

        return null;
    }

    private function countIndent(string $line): int
    {
        if (preg_match('/^(\s*)/', $line, $m) !== 1) {
            return 0;
        }

        return strlen($m[1]);
    }

    /**
     * @param list<string> $lines
     */
    private function normalizeParagraphLines(array $lines): string
    {
        while ($lines !== [] && $lines[0] === '') {
            array_shift($lines);
        }

        while ($lines !== [] && $lines[count($lines) - 1] === '') {
            array_pop($lines);
        }

        return implode("\n", $lines);
    }
}
