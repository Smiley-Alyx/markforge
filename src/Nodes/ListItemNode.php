<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class ListItemNode extends Node
{
    /**
     * @param list<Node> $children
     */
    public function __construct(
        private readonly ?bool $checked,
        private readonly array $children,
    ) {
    }

    public function checked(): ?bool
    {
        return $this->checked;
    }

    /**
     * @return list<Node>
     */
    public function children(): array
    {
        return $this->children;
    }
}
