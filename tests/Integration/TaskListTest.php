<?php

declare(strict_types=1);

namespace MarkForge\Tests\Integration;

use MarkForge\MarkForge;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MarkForge::class)]
final class TaskListTest extends TestCase
{
    public function testParsesTaskListItems(): void
    {
        $parser = new MarkForge();

        $markdown = "- [ ] Todo\n- [x] Done";

        $html = $parser->parse($markdown);

        self::assertSame(
            '<ul><li><input type="checkbox" disabled /> Todo</li><li><input type="checkbox" disabled checked /> Done</li></ul>',
            $html,
        );
    }
}
