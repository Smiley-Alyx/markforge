<?php

declare(strict_types=1);

namespace MarkForge\Tokenizer;

enum TokenType: string
{
    case Paragraph = 'paragraph';
    case Heading = 'heading';
    case HorizontalRule = 'horizontal_rule';
}
