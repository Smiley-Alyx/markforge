<?php

declare(strict_types=1);

namespace MarkForge\Tokenizer;

enum TokenType: string
{
    case Paragraph = 'paragraph';
    case Heading = 'heading';
    case HorizontalRule = 'horizontal_rule';
    case Blockquote = 'blockquote';
    case List = 'list';
    case CodeBlock = 'code_block';
}
