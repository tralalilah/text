<?php declare(strict_types=1);

namespace Midwinter\Text;

use Assert\Assertion;
use Assert\InvalidArgumentException;
use JsonSerializable;
use Midwinter\Text\Lib\RegEx;
use Throwable;

/**
 * Class Text
 * @package Midwinter\Text
 **/
final class Text implements JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param mixed $value
     * @return static
     */
    public static function create($value): self
    {
        if(is_object($value)){
            if(method_exists($value, 'toString')){
                $value = $value->toString();
            } elseif (method_exists($value, '__toString')) {
                $value = $value->__toString();
            } else {
                throw new InvalidArgumentException('Input objects must hav a "toString()"  or "__toString()" method', 400);
            }
        }
        if($value === true || $value === false){
            throw new InvalidArgumentException('true and false are not acceptable input', 400);
        }
        return new static((string)$value);
    }

    /**
     * Text constructor.
     * @param string|null $value
     */
    private function __construct(?string $value)
    {
        Assert($value !== null, 'Value should not be null');
        $this->value = $value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function length(): int
    {
        return strlen($this->value);
    }

    public function contains(string $string, bool $caseSensitive = true): bool
    {
        if (! $caseSensitive) {
            return strpos(strtolower($this->value), strtolower($string)) !== FALSE;
        }
        return strpos($this->value, $string) !== FALSE;
    }

    public function containsRegex(string $pattern): bool
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

    public function positionOf(string $string): int
    {
        Assertion::contains($this->value, $string, 'Given string does not appear');
        return (int)strpos($this->value, $string);
    }

    public function lastPositionOf(string $string): int
    {
        Assertion::contains($this->value, $string, 'Given string does not appear');
        return (int)$this->length() - strlen($string) - strpos(strrev($this->value), strrev($string));
    }

    public function characterAt(int $position): string
    {
        Assertion::lessThan($position, $this->length(), 'Position must be less than string length');
        return substr($this->value, $position, 1);
    }


    public function first(int $chars): Text
    {
        Assertion::greaterOrEqualThan($chars, 0, 'Chars cannot be negative');
        if ($chars > $this->length()){
            $chars = $this->length();
        }
        return new self(substr($this->value, 0, $chars));
    }

    public function last(int $chars): Text
    {
        Assertion::greaterOrEqualThan($chars, 0, 'Chars cannot be negative');
        if ($chars > $this->length()){
            $chars = $this->length();
        }
        return new self(substr($this->value, -$chars));
    }

    public function count(string $string): int
    {
        $escapeString = RegEx::escape($string);
        $pattern = '/' . $escapeString . '/';
        return (int)preg_match_all($pattern, $this->value);
    }

    public function before(string $string): Text
    {
       Assertion::contains($this->value, $string, 'Must contain the string argument');
       return new self(substr($this->value, 0, $this->positionOf($string)));
    }

    public function after(string $string): Text
    {
       Assertion::contains($this->value, $string, 'Must contain the string argument');
       return new self(substr($this->value, $this->positionOf($string) - $this->length() + strlen($string)));
    }

    public function allButTheFirst(int $chars): Text
    {
        Assertion::lessOrEqualThan($chars, $this->length(), '$chars must be not be longer than string length');
        return new self(substr($this->value, $chars));
    }

    public function allButTheLast(int $chars): Text
    {
        Assertion::lessOrEqualThan($chars, $this->length(), '$chars must be not be longer than string length');
        return new self(substr($this->value, 0, -$chars));
    }

    public function between(string $left, string $right, int $offset = 0): Text
    {
        $value = $this->allButTheFirst($offset)->toString();

        if ($left === $right){
            Assertion::greaterThan($this->count($left), 1, 'Only one delimiter exists');
        }
        $leftPos = strpos($value, $left);
        $rightPos = strpos($value, $right);
        if ($leftPos === FALSE || $rightPos === FALSE){
            throw new InvalidArgumentException('Both delimiters must appear in string', 400);
        }
        Assertion::lessOrEqualThan($leftPos, $rightPos, 'Left delimiter must occur before right delimiter in string');

        $leftEscaped = Lib\RegEx::escape($left);
        $rightEscaped = Lib\RegEx::escape($right);

        $pattern = "/(.*?)*({$leftEscaped}){1}(.*?)({$rightEscaped}){1}(.*)/";
        $matches = [];
        preg_match($pattern, $value, $matches);
        return Text::create($matches[3]);
    }

    /**
     * Return every string found between the two tokens. Does not support nesting delimiters.
     * @param string $left
     * @param string $right
     * @return TextCollection
     * @throws \Assert\AssertionFailedException
     */
    public function betweenMany(string $left, string $right): TextCollection
    {
        $arr = [];
        $offset = 0;
        $lastPositionOfRight = $this->lastPositionOf($right);
        while ($offset < $lastPositionOfRight){
            $between = $this->between($left, $right, $offset);
            if(strpos($between->toString(), $left) !== FALSE){
                throw new InvalidArgumentException('Nested delimiters not supported', 400);
            }

            $arr[] = $between;
            $offset = $this->positionOf($left . $between->toString() . $right) + $between->length() + strlen($right) + 1;
        }
        return TextCollection::wrap($arr);
    }

    public function uppercase(): Text
    {
        return new self(strtoupper($this->value));
    }

    public function lowercase(): Text
    {
        return new self(strtolower($this->value));
    }

    public function camelCase(): Text
    {
        $uppercaseWords = self::create(ucwords($this->value))
            ->replaceAll(' ', '')
            ->replaceSpecialCharacters('_');
        return new self(strtolower(($this->first(1))->toString()) . $uppercaseWords->allButTheFirst(1)->toString());
    }

    public function snakeCase(): Text
    {
        return new self(strtolower($this->replaceAll(' ', '_')->replaceSpecialCharacters('')->toString()));
    }

    public function titleCase(): Text
    {
        return new self(ucwords($this->toString()));
    }

    public function trim(): Text
    {
        return new self(trim($this->value));
    }

    public function replaceOne(string $textToReplace, string $replacement): Text
    {
        $offset = $this->positionOf($textToReplace);
        $length = strlen($textToReplace);
        return new self(substr_replace($this->value, $replacement, $offset, $length));
    }

    public function replaceAll(string $textToReplace, string $replacement): Text
    {
        return new self(str_replace($textToReplace, $replacement, $this->value));
    }

    public function replaceSpecialCharacters(string $replacement, string $optionalPattern = '/[^ A-Za-z0-9\-_]/'): Text
    {
        return new self (preg_replace($optionalPattern, $replacement, $this->value));
    }

    public function regexReplaceOne(string $replacement, string $pattern): Text
    {
        return new self (preg_replace($pattern, $replacement, $this->value, 1));
    }

    public function regexReplaceAll(string $replacement, string $pattern): Text
    {
        return new self (preg_replace($pattern, $replacement, $this->value));
    }

    public function swapText(string $toBeSwapped, string $swapWith): Text
    {
        $toBeSwappedPos = strpos($this->value, $toBeSwapped);
        $swapPos = strpos($this->value, $swapWith);

        Assertion::true($toBeSwappedPos !== FALSE, '"to be swapped" value not found in string');
        Assertion::true($swapPos !== FALSE, '"swap with" value not found in string');

        if($toBeSwappedPos < $swapPos){
            $left = $toBeSwapped;
            $right = $swapWith;
        } else {
            $left = $swapWith;
            $right = $toBeSwapped;
        }
        $pattern = "/(.*?)*({$left}){1}(.*?)({$right}){1}(.*)/";
        return new self (preg_replace($pattern, '$1$4$3$2$5', $this->value));
    }

    public function split(string $separator): TextCollection
    {
        $array = explode($separator, $this->value);
        return TextCollection::wrap($array);
    }
}