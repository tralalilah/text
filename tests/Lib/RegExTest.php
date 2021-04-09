<?php

namespace Lilah\TextTests\Lib;

use Assert\AssertionFailedException;
use PHPUnit\Framework\TestCase;
use Lilah\Text\Lib\RegEx;

class RegExTest extends TestCase
{
    public function testEscape(): void
    {
        $subject = 'How does this work?';
        $expected = 'How does this work\?';
        self::assertEquals($expected, RegEx::escape($subject));
    }

    /**
     * @throws AssertionFailedException
     */
    public function testSwap(): void
    {
        $subject = 'left/right';
        $left = 'left';
        $right = 'right';
        $expected = 'right/left';
        self::assertEquals($expected, RegEx::swap($left, $right, $subject));
    }

    /**
     * @throws AssertionFailedException
     */
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
