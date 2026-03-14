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

        foreach ($lines as $line) {
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

            if (trim($line) === '') {
                $this->flushParagraph($buffer, $tokens);
                continue;
            }

            $buffer[] = $line;
        }

        $this->flushParagraph($buffer, $tokens);

        return new TokenStream($tokens);
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
