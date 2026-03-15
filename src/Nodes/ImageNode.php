<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class ImageNode extends Node
{
    public function __construct(
        private readonly string $src,
        private readonly string $alt,
    ) {
    }

    public function src(): string
    {
        return $this->src;
    }

    public function alt(): string
    {
        return $this->alt;
    }
}
