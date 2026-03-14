<?php

declare(strict_types=1);

namespace MarkForge;

use MarkForge\Parser\Parser;
use MarkForge\Parser\ParserInterface;
use MarkForge\Renderer\HtmlRenderer;
use MarkForge\Renderer\RendererInterface;
use MarkForge\Tokenizer\Tokenizer;
use MarkForge\Tokenizer\TokenizerInterface;

final class MarkForge
{
    public function __construct(
        private readonly TokenizerInterface $tokenizer = new Tokenizer(),
        private readonly ParserInterface $parser = new Parser(),
        private readonly RendererInterface $renderer = new HtmlRenderer(),
    ) {
    }

    public function parse(string $markdown): string
    {
        $tokens = $this->tokenizer->tokenize($markdown);
        $ast = $this->parser->parse($tokens);

        return $this->renderer->render($ast);
    }
}
