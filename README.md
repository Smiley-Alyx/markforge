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

## Changelog

See `CHANGELOG.md`.

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
- lists (nested, multiline items, tight/loose)
- fenced code blocks
- tables
- images
- strikethrough
- paragraphs
- text

## Running tests

```bash
composer install
composer test
```

## Release / Packagist

- Publish the repository on GitHub (source: https://github.com/Smiley-Alyx/markforge).
- Create a semver tag (e.g. `v0.1.0`).
- Submit the repository to Packagist so `composer require markforge/markforge` works without VCS configuration.

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
