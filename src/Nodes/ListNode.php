<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class ListNode extends Node
{
    /**
     * @param list<ListItemNode> $items
     */
    public function __construct(
        private readonly bool $ordered,
        private readonly ?int $start,
        private readonly array $items,
    ) {
    }

    public function ordered(): bool
    {
        return $this->ordered;
    }

    public function start(): ?int
    {
        return $this->start;
    }

    /**
     * @return list<ListItemNode>
     */
    public function items(): array
    {
        return $this->items;
    }
}
