<?php

declare(strict_types=1);

namespace MarkForge\Tests\Tokenizer;

use MarkForge\Tokenizer\TokenType;
use MarkForge\Tokenizer\Tokenizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tokenizer::class)]
final class TokenizerTest extends TestCase
{
    public function testTokenizesParagraphsSeparatedByBlankLine(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("Hello\n\nWorld");

        $tokens = $stream->all();

        self::assertCount(2, $tokens);
        self::assertSame(TokenType::Paragraph, $tokens[0]->type);
        self::assertSame('Hello', $tokens[0]->value);
        self::assertSame(TokenType::Paragraph, $tokens[1]->type);
        self::assertSame('World', $tokens[1]->value);
    }
}
