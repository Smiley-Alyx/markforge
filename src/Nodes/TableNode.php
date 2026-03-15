<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class TableNode extends Node
{
    public function __construct(
        private readonly TableRowNode $header,
        /** @var list<TableRowNode> */
        private readonly array $rows,
    ) {
    }

    public function header(): TableRowNode
    {
        return $this->header;
    }

    /** @return list<TableRowNode> */
    public function rows(): array
    {
        return $this->rows;
    }
}
