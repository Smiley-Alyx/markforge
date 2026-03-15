<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class TableRowNode extends Node
{
    /** @param list<TableCellNode> $cells */
    public function __construct(
        private readonly array $cells,
    ) {
    }

    /** @return list<TableCellNode> */
    public function cells(): array
    {
        return $this->cells;
    }
}
