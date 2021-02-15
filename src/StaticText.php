<?php declare(strict_types=1);

namespace TraLaLilah\Text;

class StaticText
{
    public static function pluralizeCount(int $count, string $singular, string $plural): string
    {
        if ($count === 1) {
            return "$count $singular";
        } else {
            return "$count $plural";
        }
    }
}
