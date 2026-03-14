<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class EmphasisNode extends Node
{
    /**
     * @param list<Node> $children
     */
    public function __construct(
        private readonly int $level,
        private readonly array $children,
    ) {
    }

    public function level(): int
    {
        return $this->level;
    }

    /**
     * @return list<Node>
     */
    public function children(): array
    {
        return $this->children;
    }
}
