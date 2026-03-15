<?php

declare(strict_types=1);

namespace MarkForge\Parser;

use MarkForge\Nodes\BlockquoteNode;
use MarkForge\Nodes\CodeBlockNode;
use MarkForge\Nodes\EmphasisNode;
use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\HeadingNode;
use MarkForge\Nodes\InlineCodeNode;
use MarkForge\Nodes\LinkNode;
use MarkForge\Nodes\ListItemNode;
use MarkForge\Nodes\ListNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\TextNode;
use MarkForge\Nodes\HorizontalRuleNode;
use MarkForge\Tokenizer\TokenType;
use MarkForge\Tokenizer\TokenStream;
use MarkForge\Tokenizer\Tokenizer;

final class Parser implements ParserInterface
{
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
                /** @var list<string> $items */
                $items = $token->data['items'] ?? [];

                $listItems = [];
                foreach ($items as $itemText) {
                    $listItems[] = new ListItemNode([
                        new ParagraphNode($this->parseInlines($itemText)),
                    ]);
                }

                $children[] = new ListNode($ordered, $start, $listItems);
                continue;
            }

            if ($token->type === TokenType::CodeBlock) {
                $info = (string) ($token->data['info'] ?? '');
                $children[] = new CodeBlockNode($token->value, $info);
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
        $nodes = [];
        $i = 0;
        $len = strlen($text);

        while ($i < $len) {
            $nextCodePos = strpos($text, '`', $i);
            $nextLinkPos = strpos($text, '[', $i);
            $nextBoldPos = strpos($text, '**', $i);
            $nextItalicPos = strpos($text, '*', $i);

            $nextPos = null;
            $nextKind = null;

            if ($nextCodePos !== false) {
                $nextPos = $nextCodePos;
                $nextKind = 'code';
            }

            if ($nextLinkPos !== false) {
                if ($nextPos === null || $nextLinkPos < $nextPos) {
                    $nextPos = $nextLinkPos;
                    $nextKind = 'link';
                }
            }

            if ($nextBoldPos !== false && ($nextPos === null || $nextBoldPos < $nextPos)) {
                $nextPos = $nextBoldPos;
                $nextKind = 'bold';
            }

            if ($nextItalicPos !== false && ($nextPos === null || $nextItalicPos < $nextPos)) {
                $nextPos = $nextItalicPos;
                $nextKind = 'italic';
            }

            if ($nextPos === null) {
                $this->appendTextIfNotEmpty($nodes, substr($text, $i));
                break;
            }

            if ($nextPos > $i) {
                $this->appendTextIfNotEmpty($nodes, substr($text, $i, $nextPos - $i));
                $i = $nextPos;
            }

            if ($nextKind === 'code') {
                $close = strpos($text, '`', $i + 1);
                if ($close === false) {
                    $this->appendTextIfNotEmpty($nodes, '`');
                    $i++;
                    continue;
                }

                $inner = substr($text, $i + 1, $close - ($i + 1));
                $nodes[] = new InlineCodeNode($inner);
                $i = $close + 1;
                continue;
            }

            if ($nextKind === 'link') {
                $link = $this->tryParseLinkAt($text, $i);
                if ($link === null) {
                    $this->appendTextIfNotEmpty($nodes, '[');
                    $i++;
                    continue;
                }

                [$node, $newPos] = $link;
                $nodes[] = $node;
                $i = $newPos;
                continue;
            }

            if ($nextKind === 'bold') {
                $close = strpos($text, '**', $i + 2);
                if ($close === false) {
                    $this->appendTextIfNotEmpty($nodes, '**');
                    $i += 2;
                    continue;
                }

                $inner = substr($text, $i + 2, $close - ($i + 2));
                $nodes[] = new EmphasisNode(2, $this->parseInlines($inner));
                $i = $close + 2;
                continue;
            }

            $close = strpos($text, '*', $i + 1);
            if ($close === false) {
                $this->appendTextIfNotEmpty($nodes, '*');
                $i++;
                continue;
            }

            $inner = substr($text, $i + 1, $close - ($i + 1));
            $nodes[] = new EmphasisNode(1, $this->parseInlines($inner));
            $i = $close + 1;
        }

        return $nodes;
    }

    /**
     * @return array{0: \MarkForge\Nodes\Node, 1: int}|null
     */
    private function tryParseLinkAt(string $text, int $pos): ?array
    {
        if ($text[$pos] !== '[') {
            return null;
        }

        $closeBracket = strpos($text, ']', $pos + 1);
        if ($closeBracket === false) {
            return null;
        }

        $openParenPos = $closeBracket + 1;
        if (!isset($text[$openParenPos]) || $text[$openParenPos] !== '(') {
            return null;
        }

        $closeParen = strpos($text, ')', $openParenPos + 1);
        if ($closeParen === false) {
            return null;
        }

        $label = substr($text, $pos + 1, $closeBracket - ($pos + 1));
        $rawUrl = trim(substr($text, $openParenPos + 1, $closeParen - ($openParenPos + 1)));

        $url = $this->sanitizeUrl($rawUrl);
        if ($url === null) {
            return [new TextNode(substr($text, $pos, $closeParen - $pos + 1)), $closeParen + 1];
        }

        $children = $this->parseInlines($label);

        return [new LinkNode($url, $children), $closeParen + 1];
    }

    private function sanitizeUrl(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        if (preg_match('/^([a-zA-Z][a-zA-Z0-9+.-]*):/', $url, $m) === 1) {
            $scheme = strtolower($m[1]);
            return match ($scheme) {
                'http', 'https', 'mailto' => $url,
                default => null,
            };
        }

        return $url;
    }

    /**
     * @param list<\MarkForge\Nodes\Node> $nodes
     */
    private function appendTextIfNotEmpty(array &$nodes, string $text): void
    {
        if ($text === '') {
            return;
        }

        $nodes[] = new TextNode($text);
    }
}
