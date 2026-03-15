<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use MarkForge\MarkForge;

$parser = new MarkForge();

$markdown = "# MarkForge\n\n" .
    "Hello *it* and **bold** ~~old~~ `code` [Example](https://example.com) ![Logo](https://example.com/logo.png)\n\n" .
    "| A | B |\n|---|---|\n| 1 | 2 |\n\n" .
    "```php\necho 1;\n```\n\n" .
    "- One\n- Two\n\n" .
    "> Quote\n\n" .
    "---\n\n" .
    "World";

echo $parser->parse($markdown) . PHP_EOL;
