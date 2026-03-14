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
