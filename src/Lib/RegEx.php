<?php declare(strict_types=1);

namespace Midwinter\Text\Lib;

class RegEx
{
    public static function escape(string $string): string
    {
        return (string)preg_replace('/[.*+?^${}()|\\[\\]\\\]/', '\\\\$0', $string);
    }
}