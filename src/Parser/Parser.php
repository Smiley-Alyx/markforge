<?php

declare(strict_types=1);

namespace MarkForge\Parser;

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
                $children[] = new HeadingNode($level, [
                    new TextNode($token->value),
                ]);
                continue;
            }

            if ($token->type !== TokenType::Paragraph) {
                continue;
            }

            $children[] = new ParagraphNode([
                new TextNode($token->value),
            ]);
        }

        return new DocumentNode($children);
    }
}
