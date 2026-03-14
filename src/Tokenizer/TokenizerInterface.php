<?php

declare(strict_types=1);

namespace MarkForge\Tokenizer;

interface TokenizerInterface
{
    public function tokenize(string $markdown): TokenStream;
}
