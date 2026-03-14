<?php

declare(strict_types=1);

namespace MarkForge\Parser;

use MarkForge\Nodes\EmphasisNode;
use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\HeadingNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\TextNode;
use MarkForge\Tokenizer\TokenType;
use MarkForge\Tokenizer\TokenStream;

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
            $boldPos = strpos($text, '**', $i);
            $italicPos = strpos($text, '*', $i);

            $nextPos = null;
            $nextKind = null;

            if ($boldPos !== false) {
                $nextPos = $boldPos;
                $nextKind = 'bold';
            }

            if ($italicPos !== false && ($nextPos === null || $italicPos < $nextPos)) {
                $nextPos = $italicPos;
                $nextKind = 'italic';
            }

            if ($nextPos === null) {
                $this->appendTextIfNotEmpty($nodes, substr($text, $i));
                break;
            }

            if ($nextPos > $i) {
                $this->appendTextIfNotEmpty($nodes, substr($text, $i, $nextPos - $i));
            }

            if ($nextKind === 'bold') {
                $close = strpos($text, '**', $nextPos + 2);
                if ($close === false) {
                    $this->appendTextIfNotEmpty($nodes, substr($text, $nextPos, 2));
                    $i = $nextPos + 2;
                    continue;
                }

                $inner = substr($text, $nextPos + 2, $close - ($nextPos + 2));
                $nodes[] = new EmphasisNode(2, $this->parseInlines($inner));
                $i = $close + 2;
                continue;
            }

            $close = strpos($text, '*', $nextPos + 1);
            if ($close === false) {
                $this->appendTextIfNotEmpty($nodes, '*');
                $i = $nextPos + 1;
                continue;
            }

            $inner = substr($text, $nextPos + 1, $close - ($nextPos + 1));
            $nodes[] = new EmphasisNode(1, $this->parseInlines($inner));
            $i = $close + 1;
        }

        return $nodes;
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
