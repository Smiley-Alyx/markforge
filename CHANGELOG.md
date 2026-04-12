# Changelog

## 0.2.0

- Improve list compatibility:
  - Nested lists
  - Multiline list items
  - Tight/loose list rendering
- Improve blockquote compatibility:
  - Lazy continuation
  - Better boundaries
  - Nested blockquotes and blocks inside blockquotes
- Tables: support column alignment (`:---`, `---:`, `:---:`)
- Inline parsing:
  - Backslash escapes
  - Better emphasis delimiter rules
  - Autolinks (`<https://...>`, `<mailto:...>`)
- CI: GitHub Actions workflow running `composer test`

## 0.1.1

- Fix repository metadata links (GitHub source/issues) for Packagist/Composer.

## 0.1.0

- Initial public release.
- Block elements:
  - Paragraphs
  - ATX headings
  - Horizontal rule
  - Blockquote
  - Lists (simple, single-line items)
  - Fenced code blocks
  - Tables (simple GFM-style)
- Inline elements:
  - Bold / italic
  - Links (with basic URL sanitization)
  - Inline code
  - Images
  - Strikethrough
