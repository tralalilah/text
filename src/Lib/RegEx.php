<?php declare(strict_types=1);

namespace TraLaLilah\Text\Lib;

use Assert\Assert;
use Assert\Assertion;
use Assert\AssertionFailedException;

/**
 * Class RegEx
 *
 * Set of regex functions, to keep regexes out of Text.php.
 *
 * @category Strings
 * @package  TraLaLilah\Text\Lib
 * @author   Lilah Sturges <lilah.sturges@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/tralalilah/text
 **/
class RegEx
{
    /**
     * Escapes characters in a string that would break a regex pattern.
     *
     * @param  string $subject
     * @return string
     */
    public static function escape(string $subject): string
    {
        return (string)preg_replace('/[.*+?^${}()|\\[\\]\\\]/', '\\\\$0', $subject);
    }

    /**
     * Uses regex to swap two matched strings in another string.
     *
     * @param  string $left
     * @param  string $right
     * @param  string $subject
     * @return string
     * @throws AssertionFailedException
     */
    public static function swap(string $left, string $right, string $subject): string
    {
        Assertion::contains($subject, $left, 'String must contain left delimiter');
        Assertion::contains($subject, $right, 'String must contain right delimiter');
        Assert::that(strpos($subject, $left) < strpos($subject, $right));
        $escapeLeft = self::escape($left);
        $escapeRight = self::escape($right);
        $pattern = "/(.*?)*({$escapeLeft}){1}(.*?)({$escapeRight}){1}(.*)/";
        return (string)preg_replace($pattern, '$1$4$3$2$5', $subject);
    }

    /**
     * Uses regex to find a string between two other strings
     *
     * @param  string $left
     * @param  string $right
     * @param  string $subject
     * @return string
     * @throws AssertionFailedException
     */
    public static function between(string $left, string $right, string $subject): string
    {
        Assertion::contains($subject, $left, 'Text must contain left delimiter');
        Assertion::contains($subject, $right, 'Text must contain right delimiter');
        Assert::that(
            strpos($subject, $left) < strpos($subject, $right),
            'Left delimiter must come before right delimiter'
        );
        $leftEscaped = self::escape($left);
        $rightEscaped = self::escape($right);

        $pattern = "/(.*?)*({$leftEscaped}){1}(.*?)({$rightEscaped}){1}(.*)/";
        $matches = [];
        preg_match($pattern, $subject, $matches);
        return $matches[3];
    }

    /**
     * Uses regex to replace special character that cannot be used in slugs, variable names, etc.
     *
     * @param  string $subject
     * @param  string $replacement
     * @return string
     */
    public static function replaceSpecialCharacters(string $subject, string $replacement): string
    {
        return (string)preg_replace('/[^ A-Za-z0-9\-_]/', $replacement, $subject);
    }
}
