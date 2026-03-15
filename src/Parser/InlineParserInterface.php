<?php

declare(strict_types=1);

namespace MarkForge\Parser;

use MarkForge\Nodes\Node;

interface InlineParserInterface
{
    /**
     * @return list<Node>
     */
    public function parse(string $text): array;
}
