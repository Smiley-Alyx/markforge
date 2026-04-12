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
    public function testTokenizesTable(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("| A | B |\n|---|---|\n| 1 | 2 |");

        $tokens = $stream->all();

        self::assertCount(1, $tokens);
        self::assertSame(TokenType::Table, $tokens[0]->type);
        self::assertSame(['A', 'B'], $tokens[0]->data['header']);
        self::assertSame([['1', '2']], $tokens[0]->data['rows']);
        self::assertSame([null, null], $tokens[0]->data['align']);
    }

    public function testTokenizesTableAlignment(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("| A | B | C |\n|:---|---:|:---:|\n| 1 | 2 | 3 |");

        $tokens = $stream->all();

        self::assertCount(1, $tokens);
        self::assertSame(TokenType::Table, $tokens[0]->type);
        self::assertSame(['left', 'right', 'center'], $tokens[0]->data['align']);
    }

    public function testTokenizesFencedCodeBlock(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("```php\necho 1;\n```");

        $tokens = $stream->all();

        self::assertCount(1, $tokens);
        self::assertSame(TokenType::CodeBlock, $tokens[0]->type);
        self::assertSame('echo 1;', $tokens[0]->value);
        self::assertSame('php', $tokens[0]->data['info']);
    }

    public function testTokenizesUnorderedList(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("- One\n- Two");

        $tokens = $stream->all();

        self::assertCount(1, $tokens);
        self::assertSame(TokenType::List, $tokens[0]->type);
        self::assertFalse((bool) $tokens[0]->data['ordered']);
        self::assertSame("- One\n- Two", $tokens[0]->value);
    }

    public function testTokenizesOrderedList(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("3. Three\n4. Four");

        $tokens = $stream->all();

        self::assertCount(1, $tokens);
        self::assertSame(TokenType::List, $tokens[0]->type);
        self::assertTrue((bool) $tokens[0]->data['ordered']);
        self::assertSame(3, $tokens[0]->data['start']);
        self::assertSame("3. Three\n4. Four", $tokens[0]->value);
    }

    public function testTokenizesListWithIndentedContinuation(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("- One\n  continuation\n  - Nested\n- Two");

        $tokens = $stream->all();

        self::assertCount(1, $tokens);
        self::assertSame(TokenType::List, $tokens[0]->type);
        self::assertSame("- One\n  continuation\n  - Nested\n- Two", $tokens[0]->value);
    }

    public function testTokenizesBlockquote(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("> Quote\n> line2");

        $tokens = $stream->all();

        self::assertCount(1, $tokens);
        self::assertSame(TokenType::Blockquote, $tokens[0]->type);
        self::assertSame("Quote\nline2", $tokens[0]->value);
    }

    public function testTokenizesBlockquoteWithLazyContinuation(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("> Quote\nlazy\n> still quoted");

        $tokens = $stream->all();

        self::assertCount(1, $tokens);
        self::assertSame(TokenType::Blockquote, $tokens[0]->type);
        self::assertSame("Quote\nlazy\nstill quoted", $tokens[0]->value);
    }

    public function testBlockquoteStopsAfterBlankLine(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("> Quote\n\nNot quoted");

        $tokens = $stream->all();

        self::assertCount(2, $tokens);
        self::assertSame(TokenType::Blockquote, $tokens[0]->type);
        self::assertSame(TokenType::Paragraph, $tokens[1]->type);
    }

    public function testTokenizesHorizontalRule(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("---");

        $tokens = $stream->all();

        self::assertCount(1, $tokens);
        self::assertSame(TokenType::HorizontalRule, $tokens[0]->type);
    }

    public function testTokenizesHeading(): void
    {
        $tokenizer = new Tokenizer();
        $stream = $tokenizer->tokenize("# Title");

        $tokens = $stream->all();

        self::assertCount(1, $tokens);
        self::assertSame(TokenType::Heading, $tokens[0]->type);
        self::assertSame('Title', $tokens[0]->value);
        self::assertSame(1, $tokens[0]->data['level']);
    }

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
