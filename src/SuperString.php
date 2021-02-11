<?php declare(strict_types=1);

namespace Midwinter\SuperString;

use Assert\Assertion;
use Assert\InvalidArgumentException;
use JsonSerializable;
use Throwable;

/**
 * Class SuperString
 * @package Midwinter\SuperString
 **/
class SuperString implements JsonSerializable
{
    /**
     * @var string
     */
    private $value;

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
        if($value === null || $value === true || $value === false){
            throw new InvalidArgumentException('null, true, and false are not acceptable input', 400);
        }
        return new self((string)$value);
    }

    private function __construct(string $value)
    {
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

    public function containsRegex($pattern): bool
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
        return strpos($this->value, $string);
    }

    public function characterAt(int $position): string
    {
        Assertion::lessThan($position, $this->length(), 'Position must be less than string length');
        return substr($this->value, $position, 1);
    }


    public function first(int $chars): self
    {
        Assertion::greaterOrEqualThan($chars, 0, 'Chars cannot be negative');
        if ($chars > $this->length()){
            $chars = $this->length();
        }
        return new self(substr($this->value, 0, $chars));
    }

    public function last(int $chars): self
    {
        Assertion::greaterOrEqualThan($chars, 0, 'Chars cannot be negative');
        if ($chars > $this->length()){
            $chars = $this->length();
        }
        return new self(substr($this->value, -$chars));
    }

    public function before(string $string): self
    {
       Assertion::contains($this->value, $string, 'Must contain the string argument');
       return new self(substr($this->value, 0, $this->positionOf($string)));
    }

    public function after(string $string): self
    {
       Assertion::contains($this->value, $string, 'Must contain the string argument');
       return new self(substr($this->value, $this->positionOf($string) - $this->length() + strlen($string)));
    }

    public function allButTheFirst(int $chars)
    {
        Assertion::lessOrEqualThan($chars, $this->length(), '$chars must be not be longer than string length');
        return new self(substr($this->value, $chars));
    }

    public function allButTheLast(int $chars)
    {
        Assertion::lessOrEqualThan($chars, $this->length(), '$chars must be not be longer than string length');
        return new self(substr($this->value, 0, -$chars));
    }

    /**
     * Converts the string to uppercase
     * @return $this
     */
    public function uppercase(): self
    {
        return new self(strtoupper($this->value));
    }

    public function lowercase(): self
    {
        return new self(strtolower($this->value));
    }

    public function camelCase(): self
    {
        $ucwords = self::create(ucwords($this->value))
            ->replaceAll(' ', '')
            ->replaceSpecialCharacters('_');
        return new self(strtolower(($this->first(1))->toString()) . $ucwords->allButTheFirst(1)->toString());
    }

    public function snakeCase(): self
    {
        return new self(strtolower($this->replaceAll(' ', '_')->replaceSpecialCharacters('')->toString()));
    }

    public function titleCase(): self
    {
        return new self(ucwords($this->toString()));
    }

    public function trim(): self
    {
        return new self(trim($this->value));
    }

    public function replaceOne(string $textToReplace, string $replacement): self
    {
        $offset = $this->positionOf($textToReplace);
        $length = strlen($textToReplace);
        return new self(substr_replace($this->value, $replacement, $offset, $length));
    }

    public function replaceAll(string $textToReplace, string $replacement): self
    {
        return new self(str_replace($textToReplace, $replacement, $this->value));
    }

    public function replaceSpecialCharacters(string $replacement, string $optionalPattern = '/[^ A-Za-z0-9\-_]/'): self
    {
        return new self (preg_replace($optionalPattern, $replacement, $this->value));
    }

    public function regexReplaceOne(string $replacement, string $pattern): self
    {
        return new self (preg_replace($pattern, $replacement, $this->value, 1));
    }

    public function regexReplaceAll(string $replacement, string $pattern): self
    {
        return new self (preg_replace($pattern, $replacement, $this->value));
    }

    public function swapText(string $toBeSwapped, $swapWith): self
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

    public function split(string $separator): SuperStringCollection
    {
        $array = explode($separator, $this->value);
        return SuperStringCollection::wrap($array);
    }
}