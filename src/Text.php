<?php declare(strict_types=1);

namespace Lilah\Text;

use Assert\Assert;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Assert\InvalidArgumentException;
use JsonSerializable;
use Lilah\Text\Lib\RegEx;
use Throwable;

/**
 * Class Lilah\Text
 *
 * A useful class for developers, to assist with string manipulation.
 *
 * @category Strings
 * @package  Lilah\Text
 * @author   Lilah Sturges <lilah.sturges@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/tralalilah/text
 **/
final class Text implements JsonSerializable
{
    /**
     * private property containing the underlying string value of the Text object.
     *
     * @var string
     */
    private $value;

    /**
     * Creation/rendering methods
     */

    /**
     * Primary method for creating Text objects; returns a Text object, given
     * just about anything capable of being cast as a string.
     *
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
     * Required to implement JsonSerialize interface.
     *
     * @return mixed|string
     */
    public function jsonSerialize()
    {
        return $this->value;
    }

    /**
     * Renders thee Text object to its underlying string value.
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Property-like methods. Return scalars.
     */

    /**
     * Returns the length of the underlying string.
     * @return int
     */
    public function length(): int
    {
        return strlen($this->value);
    }

    /**
     * Returns the position of the first instance of $string in the Text object.
     *
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
     * Returns the position of the last instance of $string in the Text object.
     *
     * @param  string $string
     * @return int
     * @throws AssertionFailedException
     */
    public function lastPositionOf(string $string): int
    {
        Assertion::contains($this->value, $string, 'Given string does not appear');
        return (int)strrpos($this->value, $string);
    }

    /**
     * Returns the character at the given position in the underlying string.
     *
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
     * Returns the number of times the given string is found in the Text object.
     *
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
     * Comparison methods. Return boolean values
     */

    /**
     * Tests that one Text object has the same underlying value as another
     *
     * @param  Text $compare
     * @return bool
     */
    public function is(Text $compare): bool
    {
        return $this->value === $compare->value;
    }

    /**
     * Tests that two Text objects have similar underlying values where:
     *   - case is irrelevant
     *   - whitespace is irrelevant
     *
     * @param Text $compare
     * @return bool
     */
    public function isSimilarTo(Text $compare): bool
    {
        return $this
            ->lowercase()
            ->regexReplaceAll('', '/\s/')
            ->is($compare
                ->lowercase()
                ->regexReplaceAll('', '/\s/'));
    }

    /**
     * Tests that the provided string exists somewhere in the Text object.
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
     * Tests that the provided regex patterns matches the Text object at least once.
     *
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


    /**
     * Tests that the Text object begins with the provided string
     *
     * @param  string $test
     * @return bool
     */
    public function startsWith(string $test): bool
    {
        return strpos($this->value, $test) === 0;
    }

    /**
     * Tests that the Text object ends with the provided string
     *
     * @param  string $test
     * @return bool
     */
    public function endsWith(string $test): bool
    {
        return strpos($this->value, $test, $this->length() - strlen($test)) !== false;
    }

    /**
     * Slicing and dicing methods. Return new Text objects.
     */

    /**
     * Returns the first $chars number of characters in the Text object, as a new object.
     *
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
     * Returns the last $chars number of characters in the Text object, as a new object.
     *
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
     * Returns the portion of the Text object to be found before the provided string, as
     * a new Text object.
     *
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
     * Returns the portion of the Text object to be found after the provided string, as
     * a new Text object.
     *
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
     * Returns all but the first $chars characters of the Text object, as a new Text object.
     *
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
     * Returns all but the last $chars characters of the Text object, as a new Text object.
     *
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
     * Returns the text found between the given $left and $right delimiters,
     * as a new Text object.
     *
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
     * Return every string found between the two tokens, a TextCollection object.
     * Does not support nesting delimiters--these will throw an InvalidArgument
     * exception.
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
     * Replaces the first instance of $textToReplace with $replacement, and returns a
     * new Text object.
     *
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
     * Replaces the all instances of $textToReplace with $replacement, and returns a
     * new Text object.
     *
     * @param  string $textToReplace
     * @param  string $replacement
     * @return Text
     */
    public function replaceAll(string $textToReplace, string $replacement): Text
    {
        return new self(str_replace($textToReplace, $replacement, $this->value));
    }

    /**
     * Replaces all special characters with the provided replacement (defaulting to
     * empty string), and returns a new Text object.
     * new Text object.
     *
     * @param  string $replacement
     * @return Text
     */
    public function replaceSpecialCharacters(string $replacement = ''): Text
    {
        return new self(RegEx::replaceSpecialCharacters($this->value, $replacement));
    }

    /**
     * Replaces the first match of the provided $pattern with $replacement, and returns a
     * new Text object.
     *
     * @param  string $replacement
     * @param  string $pattern
     * @return Text
     */
    public function regexReplaceOne(string $replacement, string $pattern): Text
    {
        return new self(preg_replace($pattern, $replacement, $this->value, 1));
    }

    /**
     * Replaces all matches of the provided $pattern with $replacement, and returns a
     * new Text object.
     *
     * @param  string $replacement
     * @param  string $pattern
     * @return Text
     */
    public function regexReplaceAll(string $replacement, string $pattern): Text
    {
        return new self(preg_replace($pattern, $replacement, $this->value));
    }

    /**
     * Swaps the positions of $toBeSwapped and $swapWith in the object, and returns a new
     * Text object. Throws an exception if either string cannot be found in the object.
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
     * Transformation methods. Return new Text objects.
     */

    /**
     * Transforms the string to UPPERCASE and returns a new Text object.
     *
     * @return Text
     */
    public function uppercase(): Text
    {
        return new self(strtoupper($this->value));
    }

    /**
     * Transforms the string to lowercase and returns a new Text object.
     *
     * @return Text
     */
    public function lowercase(): Text
    {
        return new self(strtolower($this->value));
    }

    /**
     * Puts the first character of the string in lowercase and returns a new Text
     * object.
     *
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
     * Transforms the string to have Uppercase Words and returns a new Text object.
     *
     * @return Text
     */
    public function uppercaseWords(): Text
    {
        return new self(ucwords(($this->value)));
    }

    /**
     * Transforms the string to camelCase
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
     * Transforms the string to snake_case and returns a new Text object.
     *
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
     * Transforms the string to PascalCase and returns a new object
     *
     * @return Text
     */
    public function pascalCase(): Text
    {
        return $this
            ->uppercaseWords()
            ->replaceAll(' ', '');
    }

    /**
     * Trims whitespace from both sides of the string and returns a new Text object.
     *
     * @return Text
     */
    public function trim(): Text
    {
        return new self(trim($this->value));
    }

    /**
     * Pads the string on the left to the given $length with the provided $padding
     * and returns a new Text object.
     *
     * @param  int $length
     * @param  string $padding
     * @return Text
     */
    public function leftPad(int $length, string $padding = ' '): Text
    {
        return new self(str_pad($this->value, $length, $padding, STR_PAD_LEFT));
    }

    /**
     * Pads the string on the right to the given $length with the provided $padding
     * and returns a new Text object.
     *
     * @param  int $length
     * @param  string $padding
     * @return Text
     */
    public function rightPad(int $length, string $padding = ' '): Text
    {
        return new self(str_pad($this->value, $length, $padding, STR_PAD_RIGHT));
    }

    /**
     * Collection methods. Return TextCollection objects.
     */

    /**
     * Splits the string on the given $separator and returns the results as a
     * TextCollection object.
     *
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
