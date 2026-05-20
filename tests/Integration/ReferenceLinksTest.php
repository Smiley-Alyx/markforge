<?php

declare(strict_types=1);

namespace MarkForge\Tests\Integration;

use MarkForge\MarkForge;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MarkForge::class)]
final class ReferenceLinksTest extends TestCase
{
    public function testParsesReferenceStyleLinks(): void
    {
        $parser = new MarkForge();

        $markdown = "See [Example][ref] and [ref].\n\n[ref]: https://example.com \"Title\"";

        $html = $parser->parse($markdown);

        self::assertSame(
            '<p>See <a href="https://example.com">Example</a> and <a href="https://example.com">ref</a>.</p>',
            $html,
        );
    }

    public function testReferenceLabelsAreCaseInsensitiveAndCollapseWhitespace(): void
    {
        $parser = new MarkForge();

        $markdown = "[Text][A  B]\n\n[a b]: https://example.com";

        $html = $parser->parse($markdown);

        self::assertSame('<p><a href="https://example.com">Text</a></p>', $html);
    }

    public function testUnresolvedReferenceLinkRendersAsText(): void
    {
        $parser = new MarkForge();

        $html = $parser->parse('[Missing][id]');

        self::assertSame('<p>[Missing][id]</p>', $html);
    }
}
