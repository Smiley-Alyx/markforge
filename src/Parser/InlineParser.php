<?php

declare(strict_types=1);

namespace MarkForge\Parser;

use MarkForge\Nodes\EmphasisNode;
use MarkForge\Nodes\ImageNode;
use MarkForge\Nodes\InlineCodeNode;
use MarkForge\Nodes\LinkNode;
use MarkForge\Nodes\Node;
use MarkForge\Nodes\TextNode;

final class InlineParser implements InlineParserInterface
{
    /**
     * @return list<Node>
     */
    public function parse(string $text): array
    {
        $nodes = [];
        $i = 0;
        $len = strlen($text);

        while ($i < $len) {
            $nextCodePos = strpos($text, '`', $i);
            $nextImagePos = strpos($text, '![', $i);
            $nextLinkPos = strpos($text, '[', $i);
            $nextBoldPos = strpos($text, '**', $i);
            $nextItalicPos = strpos($text, '*', $i);

            $nextPos = null;
            $nextKind = null;

            if ($nextCodePos !== false) {
                $nextPos = $nextCodePos;
                $nextKind = 'code';
            }

            if ($nextImagePos !== false) {
                if ($nextPos === null || $nextImagePos < $nextPos) {
                    $nextPos = $nextImagePos;
                    $nextKind = 'image';
                }
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

            if ($nextKind === 'image') {
                $image = $this->tryParseImageAt($text, $i);
                if ($image === null) {
                    $this->appendTextIfNotEmpty($nodes, '!');
                    $i++;
                    continue;
                }

                [$node, $newPos] = $image;
                $nodes[] = $node;
                $i = $newPos;
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
                $nodes[] = new EmphasisNode(2, $this->parse($inner));
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
            $nodes[] = new EmphasisNode(1, $this->parse($inner));
            $i = $close + 1;
        }

        return $nodes;
    }

    /**
     * @return array{0: Node, 1: int}|null
     */
    private function tryParseImageAt(string $text, int $pos): ?array
    {
        if (!isset($text[$pos], $text[$pos + 1]) || $text[$pos] !== '!' || $text[$pos + 1] !== '[') {
            return null;
        }

        $closeBracket = strpos($text, ']', $pos + 2);
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

        $alt = substr($text, $pos + 2, $closeBracket - ($pos + 2));
        $rawUrl = trim(substr($text, $openParenPos + 1, $closeParen - ($openParenPos + 1)));

        $url = $this->sanitizeUrl($rawUrl);
        if ($url === null) {
            return [new TextNode(substr($text, $pos, $closeParen - $pos + 1)), $closeParen + 1];
        }

        return [new ImageNode($url, $alt), $closeParen + 1];
    }

    /**
     * @return array{0: Node, 1: int}|null
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

        $children = $this->parse($label);

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
     * @param list<Node> $nodes
     */
    private function appendTextIfNotEmpty(array &$nodes, string $text): void
    {
        if ($text === '') {
            return;
        }

        $nodes[] = new TextNode($text);
    }
}
