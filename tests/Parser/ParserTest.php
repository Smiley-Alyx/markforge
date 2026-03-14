<?php

declare(strict_types=1);

namespace MarkForge\Tests\Parser;

use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\TextNode;
use MarkForge\Parser\Parser;
use MarkForge\Tokenizer\Tokenizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Parser::class)]
final class ParserTest extends TestCase
{
    public function testParsesTokenizerOutputIntoDocumentAst(): void
    {
        $tokenizer = new Tokenizer();
        $parser = new Parser();

        $tokens = $tokenizer->tokenize("Hello\n\nWorld");
        $document = $parser->parse($tokens);

        self::assertInstanceOf(DocumentNode::class, $document);
        self::assertCount(2, $document->children());

        $p1 = $document->children()[0];
        $p2 = $document->children()[1];

        self::assertInstanceOf(ParagraphNode::class, $p1);
        self::assertInstanceOf(ParagraphNode::class, $p2);

        self::assertCount(1, $p1->children());
        self::assertInstanceOf(TextNode::class, $p1->children()[0]);
        self::assertSame('Hello', $p1->children()[0]->text());
    }
}
