<?php declare(strict_types=1);

namespace TraLaLilah\Text;

use Assert\Assert;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Assert\InvalidArgumentException;
use JsonSerializable;
use TraLaLilah\Text\Lib\RegEx;
use Throwable;

/**
 * Primary class for doing string manipulation.
 *
 * A useful class for developers, to assist with strings.
 *
 * @category Strings
 * @package  TraLaLilah\Text
 * @author   Lilah Sturges <lilah.sturges@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/tralalilah/text
 **/
final class Text implements JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param  mixed $value
     * @return static
     */
    public static function create($value): self
    {
        if (is_object($value)) {
            if (method_exists($value, 'toString')) {
                $value = $value->toString();
            } elseif (method_exists($value, '__toString')) {
                $value = $value->__toString();
            } else {
                throw new InvalidArgumentException(
                    'Input objects must hav a "toString()"  or "__toString()" method',
                    400
                );
            }
        }
        if ($value === true || $value === false) {
            throw new InvalidArgumentException(
                'true and false are not acceptable input',
                400
            );
        }
        return new static((string)$value);
    }

    /**
     * Text constructor.
     *
     * @param  string|null $value
     */
    private function __construct(?string $value)
    {
        Assert($value !== null, 'Value should not be null');
        $this->value = $value;
    }

    /**
     * Clones the object into a new one
     *
     * @return self
     */
    public function clone(): self
    {
        return new self($this->value);
    }

    /**
     * @return mixed|string
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function length(): int
    {
        return strlen($this->value);
    }

    /**
     * @param  string $string
     * @param  bool   $caseSensitive
     * @return bool
     */
    public function contains(string $string, bool $caseSensitive = true): bool
    {
        if (! $caseSensitive) {
            return strpos(strtolower($this->value), strtolower($string)) !== false;
        }
        return strpos($this->value, $string) !== false;
    }

    /**
     * @param  string $pattern
     * @return bool
     */
    public function matchesRegex(string $pattern): bool
    {
        try {
            $result = preg_match($pattern, $this->value);
            if ($result === 1) {
                return true;
            } else {
                return false;
            }
        } catch (Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), 400);
        }
    }

    public function startsWith(string $test): bool
    {
        return strpos($this->value, $test) === 0;
    }

    public function endsWith(string $test): bool
    {
        return strpos($this->value, $test, $this->length() - strlen($test)) !== false;
    }

    /**
     * @param  string $string
     * @return int
     * @throws AssertionFailedException
     */
    public function positionOf(string $string): int
    {
        Assertion::contains($this->value, $string, 'Given string does not appear');
        return (int)strpos($this->value, $string);
    }

    /**
     * @param  string $string
     * @return int
     * @throws AssertionFailedException
     */
    public function lastPositionOf(string $string): int
    {
        Assertion::contains($this->value, $string, 'Given string does not appear');
        return strrpos($this->value, $string);
    }

    /**
     * @param  int $position
     * @return string
     * @throws AssertionFailedException
     */
    public function characterAt(int $position): string
    {
        Assertion::greaterOrEqualThan($position, 0, 'Position cannot be negative');
        Assertion::lessThan($position, $this->length(), 'Position must be less than string length');
        return substr($this->value, $position, 1);
    }

    /**
     * @param  int $chars
     * @return Text
     * @throws AssertionFailedException
     */
    public function first(int $chars): Text
    {
        Assertion::greaterOrEqualThan($chars, 0, 'Chars cannot be negative');
        if ($chars > $this->length()) {
            $chars = $this->length();
        }
        return new self(substr($this->value, 0, $chars));
    }

    /**
     * @param  int $chars
     * @return Text
     * @throws AssertionFailedException
     */
    public function last(int $chars): Text
    {
        Assertion::greaterOrEqualThan($chars, 0, 'Chars cannot be negative');
        if ($chars > $this->length()) {
            $chars = $this->length();
        }
        return new self(substr($this->value, -$chars));
    }

    /**
     * @param  string $string
     * @return int
     */
    public function count(string $string): int
    {
        $escapeString = RegEx::escape($string);
        $pattern = '/' . $escapeString . '/';
        return (int)preg_match_all($pattern, $this->value);
    }

    /**
     * @param  string $string
     * @return Text
     * @throws AssertionFailedException
     */
    public function before(string $string): Text
    {
        Assertion::contains($this->value, $string, 'Must contain the string argument');
        return new self(substr($this->value, 0, $this->positionOf($string)));
    }

    /**
     * @param  string $string
     * @return Text
     * @throws AssertionFailedException
     */
    public function after(string $string): Text
    {
        Assertion::contains($this->value, $string, 'Must contain the string argument');
        return new self(substr($this->value, $this->positionOf($string) - $this->length() + strlen($string)));
    }

    /**
     * @param  int $chars
     * @return Text
     * @throws AssertionFailedException
     */
    public function allButTheFirst(int $chars): Text
    {
        Assertion::lessOrEqualThan($chars, $this->length(), '$chars must be not be longer than string length');
        return new self(substr($this->value, $chars));
    }

    /**
     * @param  int $chars
     * @return Text
     * @throws AssertionFailedException
     */
    public function allButTheLast(int $chars): Text
    {
        Assertion::lessOrEqualThan($chars, $this->length(), '$chars must be not be longer than string length');
        return new self(substr($this->value, 0, -$chars));
    }

    /**
     * @param  string $left
     * @param  string $right
     * @param  int    $offset
     * @return Text
     * @throws AssertionFailedException
     */
    public function between(string $left, string $right, int $offset = 0): Text
    {
        $value = $this->allButTheFirst($offset)->toString();

        if ($left === $right) {
            Assertion::greaterThan($this->count($left), 1, 'Only one delimiter exists');
        }
        Assertion::contains($this->value, $left, '"left" delimiter must exist in text');
        Assertion::contains($this->value, $right, '"right" delimiter must exist in text');
        Assert::that(
            $this->positionOf($left) < $this->positionOf($right),
            'Left delimiter must occur before right delimiter in text'
        );

        return Text::create(RegEx::between($left, $right, $value));
    }

    /**
     * Return every string found between the two tokens. Does not support nesting delimiters.
     *
     * @param  string $left
     * @param  string $right
     * @return TextCollection
     * @throws AssertionFailedException
     */
    public function betweenMany(string $left, string $right): TextCollection
    {
        $arr = [];
        $offset = 0;
        $lastPositionOfRight = $this->lastPositionOf($right);
        while ($offset < $lastPositionOfRight) {
            $between = $this->between($left, $right, $offset);
            if (strpos($between->toString(), $left) !== false) {
                throw new InvalidArgumentException('Nested delimiters not supported', 400);
            }

            $arr[] = $between;
            $offset = $this->positionOf($left . $between->toString() . $right) +
                $between->length() + strlen($right) + 1;
        }
        return TextCollection::wrap($arr);
    }

    public function concatenate(Text $textToConcatenate): Text
    {
        return new self($this->value . $textToConcatenate->value);
    }

    /**
     * @return Text
     */
    public function uppercase(): Text
    {
        return new self(strtoupper($this->value));
    }

    /**
     * @return Text
     */
    public function lowercase(): Text
    {
        return new self(strtolower($this->value));
    }

    /**
     * @return Text
     * @throws AssertionFailedException
     */
    public function lowercaseFirst(): Text
    {
        return $this
            ->first(1)
            ->lowercase()
            ->concatenate(
                $this
                    ->uppercaseWords()
                    ->allButTheFirst(1)
            );
    }

    /**
     * @return Text
     */
    public function uppercaseWords(): Text
    {
        return new self(ucwords(($this->value)));
    }

    /**
     * @return Text
     * @throws AssertionFailedException
     */
    public function camelCase(): Text
    {
        return $this
            ->uppercaseWords()
            ->replaceSpecialCharacters('')
            ->replaceAll(' ', '')
            ->lowercaseFirst();
    }

    /**
     * @return Text
     */
    public function snakeCase(): Text
    {
        return $this
            ->lowercase()
            ->replaceAll(' ', '_')
            ->replaceSpecialCharacters('');
    }

    public function slug(): Text
    {
        return $this
            ->lowercase()
            ->replaceAll(' ', '-')
            ->replaceSpecialCharacters('');
    }

    /**
     * @return Text
     */
    public function titleCase(): Text
    {
        return $this->uppercaseWords();
    }

    /**
     * @return Text
     */
    public function trim(): Text
    {
        return new self(trim($this->value));
    }

    public function leftPad(int $length, string $padding = ' '): Text
    {
        return new self(str_pad($this->value, $length, $padding, STR_PAD_LEFT));
    }

    public function rightPad(int $length, string $padding = ' '): Text
    {
        return new self(str_pad($this->value, $length, $padding, STR_PAD_RIGHT));
    }

    /**
     * @param  string $textToReplace
     * @param  string $replacement
     * @return Text
     * @throws AssertionFailedException
     */
    public function replaceOne(string $textToReplace, string $replacement): Text
    {
        $offset = $this->positionOf($textToReplace);
        $length = strlen($textToReplace);
        return new self(substr_replace($this->value, $replacement, $offset, $length));
    }

    /**
     * @param  string $textToReplace
     * @param  string $replacement
     * @return Text
     */
    public function replaceAll(string $textToReplace, string $replacement): Text
    {
        return new self(str_replace($textToReplace, $replacement, $this->value));
    }

    /**
     * @param  string $replacement
     * @return Text
     */
    public function replaceSpecialCharacters(string $replacement = ''): Text
    {
        return new self(RegEx::replaceSpecialCharacters($this->value, $replacement));
    }

    /**
     * @param  string $replacement
     * @param  string $pattern
     * @return Text
     */
    public function regexReplaceOne(string $replacement, string $pattern): Text
    {
        return new self(preg_replace($pattern, $replacement, $this->value, 1));
    }

    /**
     * @param  string $replacement
     * @param  string $pattern
     * @return Text
     */
    public function regexReplaceAll(string $replacement, string $pattern): Text
    {
        return new self(preg_replace($pattern, $replacement, $this->value));
    }

    /**
     * @param  string[] $tokens
     * @param  array[]  $replaceData
     * @param  string   $leftDelimiter
     * @param  string   $rightDelimiter
     * @return TextCollection
     * @throws AssertionFailedException
     */
    public function mailMerge(
        array $tokens,
        array $replaceData,
        string $leftDelimiter,
        string $rightDelimiter
    ): TextCollection {
        $collection = TextCollection::empty();
        foreach ($replaceData as $replacementRow) {
            Assertion::eq(
                count($tokens),
                count($replacementRow),
                'Replacement arrays must be same length as token array'
            );
            $count = count($tokens);
            $text = $this->clone();
            for ($i = 0; $i < $count; $i ++) {
                $textToReplace = $leftDelimiter . $tokens[$i] . $rightDelimiter;
                $text = $text->replaceAll($textToReplace, $replacementRow[$i]);
            }
            $collection->add($text);
        }
        return $collection;
    }

    /**
     * @param  string $toBeSwapped
     * @param  string $swapWith
     * @return Text
     * @throws AssertionFailedException
     */
    public function swap(string $toBeSwapped, string $swapWith): Text
    {
        Assertion::contains($this->value, $toBeSwapped, '"to be swapped" value not found in string');
        Assertion::contains($this->value, $swapWith, '"swap with" value not found in string');

        $toBeSwappedPos = strpos($this->value, $toBeSwapped);
        $swapPos = strpos($this->value, $swapWith);

        if ($toBeSwappedPos < $swapPos) {
            $left = $toBeSwapped;
            $right = $swapWith;
        } else {
            $left = $swapWith;
            $right = $toBeSwapped;
        }
        return new self(RegEx::swap($left, $right, $this->value));
    }

    /**
     * @param  string $separator
     * @return TextCollection
     * @throws AssertionFailedException
     */
    public function split(string $separator): TextCollection
    {
        $array = explode($separator, $this->value);
        return TextCollection::wrap($array);
    }
}
