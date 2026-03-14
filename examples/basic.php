<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use MarkForge\MarkForge;

$parser = new MarkForge();

$markdown = "Hello, world!\n\nSecond paragraph.";

echo $parser->parse($markdown) . PHP_EOL;
