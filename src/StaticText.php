<?php declare(strict_types=1);

namespace TraLaLilah\Text;

class StaticText
{
    /**
     * Takes a $count, a $singular noun, and a $plural noun and returns a
     * string using the correct pluralizating for the count given.
     *
     * @param  int $count
     * @param  string $singular
     * @param  string $plural
     * @return string
     */
    public static function pluralizeCount(int $count, string $singular, string $plural): string
    {
        if ($count === 1) {
            return "$count $singular";
        } else {
            return "$count $plural";
        }
    }
}
