<?php

declare(strict_types=1);

namespace MarkForge\Tokenizer;

final class Tokenizer implements TokenizerInterface
{
    public function tokenize(string $markdown): TokenStream
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $markdown);
        $lines = explode("\n", $normalized);

        $tokens = [];
        $buffer = [];

        $count = count($lines);
        for ($idx = 0; $idx < $count; $idx++) {
            $line = $lines[$idx];

            $codeFence = $this->tryConsumeFencedCodeBlock($lines, $idx);
            if ($codeFence !== null) {
                $this->flushParagraph($buffer, $tokens);
                [$token, $newIdx] = $codeFence;
                $tokens[] = $token;
                $idx = $newIdx;
                continue;
            }

            $table = $this->tryConsumeTable($lines, $idx);
            if ($table !== null) {
                $this->flushParagraph($buffer, $tokens);
                [$token, $newIdx] = $table;
                $tokens[] = $token;
                $idx = $newIdx;
                continue;
            }

            $heading = $this->tryParseHeading($line);
            if ($heading !== null) {
                $this->flushParagraph($buffer, $tokens);
                $tokens[] = $heading;
                continue;
            }

            if ($this->isHorizontalRule($line)) {
                $this->flushParagraph($buffer, $tokens);
                $tokens[] = new Token(TokenType::HorizontalRule, '');
                continue;
            }

            if ($this->isBlockquoteStart($line)) {
                $this->flushParagraph($buffer, $tokens);
                [$token, $newIdx] = $this->consumeBlockquote($lines, $idx);
                $tokens[] = $token;
                $idx = $newIdx;
                continue;
            }

            $list = $this->tryConsumeList($lines, $idx);
            if ($list !== null) {
                $this->flushParagraph($buffer, $tokens);
                [$token, $newIdx] = $list;
                $tokens[] = $token;
                $idx = $newIdx;
                continue;
            }

            if (trim($line) === '') {
                $this->flushParagraph($buffer, $tokens);
                continue;
            }

            $buffer[] = $line;
        }

        $this->flushParagraph($buffer, $tokens);

        return new TokenStream($tokens);
    }

    /**
     * @param list<string> $lines
     * @return array{0: Token, 1: int}|null
     */
    private function tryConsumeTable(array $lines, int $startIdx): ?array
    {
        $headerLine = $lines[$startIdx] ?? '';
        $separatorLine = $lines[$startIdx + 1] ?? null;
        if ($separatorLine === null) {
            return null;
        }

        if (!$this->looksLikeTableRow($headerLine)) {
            return null;
        }

        if (!$this->isTableSeparatorLine($separatorLine)) {
            return null;
        }

        $headerCells = $this->splitTableRow($headerLine);
        $columnCount = count($headerCells);
        if ($columnCount === 0) {
            return null;
        }

        $rows = [];
        $idx = $startIdx + 2;
        $max = count($lines);
        while ($idx < $max) {
            $line = $lines[$idx];
            if (trim($line) === '') {
                break;
            }

            if (!$this->looksLikeTableRow($line)) {
                break;
            }

            $cells = $this->splitTableRow($line);
            if (count($cells) !== $columnCount) {
                break;
            }

            $rows[] = $cells;
            $idx++;
        }

        $token = new Token(TokenType::Table, '', [
            'header' => $headerCells,
            'rows' => $rows,
        ]);

        return [$token, $idx - 1];
    }

    private function looksLikeTableRow(string $line): bool
    {
        return str_contains($line, '|');
    }

    private function isTableSeparatorLine(string $line): bool
    {
        $trimmed = trim($line);
        if ($trimmed === '') {
            return false;
        }

        $noSpaces = str_replace(' ', '', $trimmed);
        $parts = $this->splitTableRow($noSpaces);
        if ($parts === []) {
            return false;
        }

        foreach ($parts as $part) {
            if ($part === '') {
                return false;
            }
            if (preg_match('/^:?-{3,}:?$/', $part) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function splitTableRow(string $line): array
    {
        $trimmed = trim($line);
        if ($trimmed === '') {
            return [];
        }

        if (str_starts_with($trimmed, '|')) {
            $trimmed = substr($trimmed, 1);
        }
        if (str_ends_with($trimmed, '|')) {
            $trimmed = substr($trimmed, 0, -1);
        }

        $raw = explode('|', $trimmed);
        $cells = [];
        foreach ($raw as $cell) {
            $cells[] = trim($cell);
        }

        return $cells;
    }

    private function isBlockquoteStart(string $line): bool
    {
        return preg_match('/^>\s?/', $line) === 1;
    }

    /**
     * @param list<string> $lines
     * @return array{0: Token, 1: int}|null
     */
    private function tryConsumeFencedCodeBlock(array $lines, int $startIdx): ?array
    {
        $line = $lines[$startIdx] ?? '';

        if (preg_match('/^```\s*(.*)$/', $line, $m) !== 1) {
            return null;
        }

        $info = trim($m[1]);
        $codeLines = [];

        $idx = $startIdx + 1;
        $max = count($lines);
        while ($idx < $max) {
            $current = $lines[$idx];
            if (preg_match('/^```\s*$/', $current) === 1) {
                break;
            }

            $codeLines[] = $current;
            $idx++;
        }

        $code = implode("\n", $codeLines);
        $token = new Token(TokenType::CodeBlock, $code, ['info' => $info]);

        if ($idx >= $max) {
            return [$token, $max - 1];
        }

        return [$token, $idx];
    }

    /**
     * @param list<string> $lines
     * @return array{0: Token, 1: int}|null
     */
    private function tryConsumeList(array $lines, int $startIdx): ?array
    {
        $line = $lines[$startIdx] ?? '';

        $unordered = $this->parseUnorderedListItem($line);
        $ordered = $this->parseOrderedListItem($line);

        if ($unordered === null && $ordered === null) {
            return null;
        }

        $orderedMode = $ordered !== null;
        $start = $orderedMode ? $ordered['start'] : null;

        $blockLines = [];
        $idx = $startIdx;
        $max = count($lines);

        while ($idx < $max) {
            $current = $lines[$idx];

            if (trim($current) === '') {
                $next = $lines[$idx + 1] ?? null;
                if ($next === null) {
                    break;
                }

                if ($this->isAnyListItemLine($next) || $this->isIndentedContinuationLine($next)) {
                    $blockLines[] = '';
                    $idx++;
                    continue;
                }

                break;
            }

            if ($this->isAnyListItemLine($current) || $this->isIndentedContinuationLine($current)) {
                $blockLines[] = $current;
                $idx++;
                continue;
            }

            break;
        }

        $token = new Token(TokenType::List, implode("\n", $blockLines), [
            'ordered' => $orderedMode,
            'start' => $start,
        ]);

        return [$token, $idx - 1];
    }

    private function isAnyListItemLine(string $line): bool
    {
        return $this->parseUnorderedListItem($line) !== null || $this->parseOrderedListItem($line) !== null;
    }

    private function isIndentedContinuationLine(string $line): bool
    {
        return preg_match('/^(\t| {2,})\S/', $line) === 1;
    }

    private function parseUnorderedListItem(string $line): ?string
    {
        if (preg_match('/^\s*[-*+]\s+(.*)$/', $line, $m) !== 1) {
            return null;
        }

        return $m[1];
    }

    /**
     * @return array{start: int, text: string}|null
     */
    private function parseOrderedListItem(string $line): ?array
    {
        if (preg_match('/^\s*(\d+)\.\s+(.*)$/', $line, $m) !== 1) {
            return null;
        }

        return ['start' => (int) $m[1], 'text' => $m[2]];
    }

    /**
     * @param list<string> $lines
     * @return array{0: Token, 1: int}
     */
    private function consumeBlockquote(array $lines, int $startIdx): array
    {
        $innerLines = [];
        $idx = $startIdx;
        $max = count($lines);

        $previousWasBlank = false;

        while ($idx < $max) {
            $line = $lines[$idx];

            if ($this->isBlockquoteStart($line)) {
                $innerLines[] = $this->stripBlockquotePrefix($line);
                $previousWasBlank = false;
                $idx++;
                continue;
            }

            if (trim($line) === '') {
                $innerLines[] = '';
                $previousWasBlank = true;
                $idx++;
                continue;
            }

            if ($previousWasBlank) {
                break;
            }

            $innerLines[] = $line;
            $previousWasBlank = false;
            $idx++;
            continue;

        }

        $inner = implode("\n", $innerLines);

        return [new Token(TokenType::Blockquote, $inner), $idx - 1];
    }

    private function stripBlockquotePrefix(string $line): string
    {
        return preg_replace('/^>\s?/', '', $line) ?? '';
    }

    private function tryParseHeading(string $line): ?Token
    {
        if (!preg_match('/^(#{1,6})\s+(.*)$/', $line, $m)) {
            return null;
        }

        $level = strlen($m[1]);
        $text = $m[2];

        return new Token(TokenType::Heading, $text, ['level' => $level]);
    }

    private function isHorizontalRule(string $line): bool
    {
        $trimmed = trim($line);
        if ($trimmed === '') {
            return false;
        }

        $noSpaces = str_replace(' ', '', $trimmed);
        if (strlen($noSpaces) < 3) {
            return false;
        }

        if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $noSpaces) !== 1) {
            return false;
        }

        return true;
    }

    /**
     * @param list<string> $buffer
     * @param list<Token> $tokens
     */
    private function flushParagraph(array &$buffer, array &$tokens): void
    {
        if ($buffer === []) {
            return;
        }

        $text = implode("\n", $buffer);
        $tokens[] = new Token(TokenType::Paragraph, $text);
        $buffer = [];
    }
}
