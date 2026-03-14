<?php

declare(strict_types=1);

namespace MarkForge\Tokenizer;

final class Token
{
    public function __construct(
        public readonly TokenType $type,
        public readonly string $value,
        /**
         * @var array<string, mixed>
         */
        public readonly array $data = [],
    ) {
    }
}
