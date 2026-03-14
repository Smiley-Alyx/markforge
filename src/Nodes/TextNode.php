<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class TextNode extends Node
{
    public function __construct(
        private readonly string $text,
    ) {
    }

    public function text(): string
    {
        return $this->text;
    }
}
