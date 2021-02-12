<?php

namespace TraLaLilah\Text\tests\Lib;

use PHPUnit\Framework\TestCase;
use TraLaLilah\Text\Lib\RegEx;

class RegExTest extends TestCase
{
    public function testEscape(): void
    {
        $subject = 'How does this work?';
        $expected = 'How does this work\?';
        self::assertEquals($expected, RegEx::escape($subject));
    }

    public function testSwap(): void
    {
        $subject = 'left/right';
        $left = 'left';
        $right = 'right';
        $expected = 'right/left';
        self::assertEquals($expected, RegEx::swap($left, $right, $subject));
    }

    public function testBetween(): void
    {
        $subject = 'Something is [between] the square braces.';
        $left = '[';
        $right = ']';
        $expected = 'between';
        self::assertEquals($expected, RegEx::between($left, $right, $subject));
    }

    public function testReplaceSpecialCharacters(): void
    {
        $subject = 'This& is* a{ string';
        $replacement = '';
        $expected = 'This is a string';
        self::assertEquals($expected, RegEx::replaceSpecialCharacters($subject, $replacement));
    }
}