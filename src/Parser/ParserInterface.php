<?php

declare(strict_types=1);

namespace MarkForge\Parser;

use MarkForge\Nodes\DocumentNode;
use MarkForge\Tokenizer\TokenStream;

interface ParserInterface
{
    public function parse(TokenStream $tokens): DocumentNode;
}
