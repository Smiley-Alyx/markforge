# MarkForge

MarkForge is an open-source Markdown to HTML parser for PHP 8.3+.

This repository currently contains a minimal, working pipeline (Tokenizer → Parser → AST → Renderer) with incremental feature development.

## Requirements

- PHP 8.3+
- Composer

## Installation

```bash
composer require markforge/markforge
```

## Usage

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

$parser = new MarkForge\MarkForge();
$html = $parser->parse("Hello, world!\n\nSecond paragraph.");

echo $html;
```

See also: `examples/basic.php`.

## Supported Markdown (current)

- headings
- bold
- italic
- links
- inline code
- horizontal rule
- blockquote
- lists
- fenced code blocks
- tables
- paragraphs
- text

## Running tests

```bash
composer install
vendor/bin/phpunit
```

## Project structure

```text
src/
  AST/
  Exceptions/
  Nodes/
  Parser/
  Renderer/
  Tokenizer/
tests/
```

## Security

This project is a Markdown renderer. If you render untrusted user input, you must review the HTML output security implications carefully.
