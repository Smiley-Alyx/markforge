# markforge

MarkForge is an open-source Markdown to HTML parser for PHP 8.3+.

This repository currently contains the project scaffold (Composer package, PHPUnit config, CI workflow). Parser implementation will be added incrementally.

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

## Supported Markdown (current)

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
