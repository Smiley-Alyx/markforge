<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class InlineCodeNode extends Node
{
    public function __construct(
        private readonly string $code,
    ) {
    }

    public function code(): string
    {
        return $this->code;
    }
}
