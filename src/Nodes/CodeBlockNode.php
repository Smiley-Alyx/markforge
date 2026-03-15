<?php

declare(strict_types=1);

namespace MarkForge\Nodes;

final class CodeBlockNode extends Node
{
    public function __construct(
        private readonly string $code,
        private readonly string $info,
    ) {
    }

    public function code(): string
    {
        return $this->code;
    }

    public function info(): string
    {
        return $this->info;
    }
}
