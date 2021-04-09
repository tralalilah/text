<?php declare(strict_types=1);

namespace Lilah\TextTests;

use PHPUnit\Framework\TestCase;
use Lilah\Text\StaticText;

class StaticTextTest extends TestCase
{
    public function testPluralizeCount(): void
    {
        $count = 1;
        $singular = 'item';
        $plural = 'items';
        $expected = '1 item';
        self::assertEquals($expected, StaticText::pluralizeCount($count, $singular, $plural));

        $count = 2;
        $singular = 'item';
        $plural = 'items';
        $expected = '2 items';
        self::assertEquals($expected, StaticText::pluralizeCount($count, $singular, $plural));

        $count = 0;
        $singular = 'item';
        $plural = 'items';
        $expected = '0 items';
        self::assertEquals($expected, StaticText::pluralizeCount($count, $singular, $plural));
    }
}
