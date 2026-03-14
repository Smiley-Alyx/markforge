<?php

declare(strict_types=1);

namespace MarkForge\Renderer;

use MarkForge\Nodes\DocumentNode;

interface RendererInterface
{
    public function render(DocumentNode $document): string;
}
