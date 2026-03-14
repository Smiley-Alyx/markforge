<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class LinkNode extends Node
{
    /**
     * @param list<Node> $children
     */
    public function __construct(
        private readonly string $url,
        private readonly array $children,
    ) {
    }

    public function url(): string
    {
        return $this->url;
    }

    /**
     * @return list<Node>
     */
    public function children(): array
    {
        return $this->children;
    }
}
