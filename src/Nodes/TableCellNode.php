<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class TableCellNode extends Node
{
    /** @param list<Node> $children */
    public function __construct(
        private readonly bool $header,
        private readonly ?string $align,
        private readonly array $children,
    ) {
    }

    public function header(): bool
    {
        return $this->header;
    }

    public function align(): ?string
    {
        return $this->align;
    }

    /** @return list<Node> */
    public function children(): array
    {
        return $this->children;
    }
}
