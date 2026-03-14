<?php

declare(strict_types=1);

namespace MarkForge\Tokenizer;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Token>
 */
final class TokenStream implements IteratorAggregate, Countable
{
    /**
     * @param list<Token> $tokens
     */
    public function __construct(
        private readonly array $tokens,
    ) {
    }

    /**
     * @return list<Token>
     */
    public function all(): array
    {
        return $this->tokens;
    }

    public function count(): int
    {
        return count($this->tokens);
    }

    public function getIterator(): Traversable
    {
        yield from $this->tokens;
    }
}
