<?php

declare(strict_types=1);

namespace MarkForge\Parser;

use MarkForge\Nodes\BlockquoteNode;
use MarkForge\Nodes\CodeBlockNode;
use MarkForge\Nodes\DocumentNode;
use MarkForge\Nodes\HeadingNode;
use MarkForge\Nodes\ListItemNode;
use MarkForge\Nodes\ListNode;
use MarkForge\Nodes\ParagraphNode;
use MarkForge\Nodes\HorizontalRuleNode;
use MarkForge\Tokenizer\TokenType;
use MarkForge\Tokenizer\TokenStream;
use MarkForge\Tokenizer\Tokenizer;

final class Parser implements ParserInterface
{
    public function __construct(
        private readonly InlineParserInterface $inlineParser = new InlineParser(),
    ) {
    }

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
        return $this->inlineParser->parse($text);
    }
}
