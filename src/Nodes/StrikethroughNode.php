<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class StrikethroughNode extends Node
{
    /**
     * @param list<Node> $children
     */
    public function __construct(
        private readonly array $children,
    ) {
    }

    /**
     * @return list<Node>
     */
    public function children(): array
    {
        return $this->children;
    }
}
