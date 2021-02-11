<?php declare(strict_types=1);

namespace Midwinter\SuperString;

use Assert\Assertion;
use Countable;
use JsonSerializable;
use Prophecy\Exception\Doubler\MethodNotFoundException;
use Tightenco\Collect\Support\Collection;

/**
 * Class SuperStringCollection
 * @package Midwinter\SuperString
 *
 * @method SuperStringCollection uppercase() Converts all strings to uppercase
 * @method SuperStringCollection lowercase() Converts all strings to lowercase
 * @method SuperStringCollection snakeCase() Converts all strings to snake_case
 * @method SuperStringCollection camelCase() Converts all strings to camelCase
 * @method SuperStringCollection titleCase() Converts all strings to Title Case
 * @method SuperStringCollection replaceSpecialCharacters(string $replacement) Replace special characters with provided replacement
 * @method SuperStringCollection trim() Trims all strings
 * @method SuperStringCollection replaceAll(string $textToReplace, string $replacement) Replaces all instances in each string
 * @method SuperStringCollection regexReplaceAll(string $replacement, string $pattern) Replaces all pattern matches in each string
 * @method missingMethod() Does not exist. For testing purposes only.
 */
class SuperStringCollection implements Countable, JsonSerializable
{
    private const AVAILABLE_METHODS = [
        'uppercase',
        'lowercase',
        'snakeCase',
        'camelCase',
        'titleCase',
        'replaceSpecialCharacters',
        'trim',
        'replaceAll',
        'regexReplaceAll'
    ];

    /**
     * @var Collection|SuperString[]
     */
    private $collection;

    /**
     * Returns a new instance of SuperStringCollection.
     * @param mixed $array Contents can be of mixed types. Associative arrays will lose string keys.
     * @return SuperStringCollection
     * @throws \Assert\AssertionFailedException
     */
    public static function wrap($array)
    {
        Assertion::isArray($array, 'Input must be an array');
        return new self($array);
    }

    /**
     * SuperStringCollection constructor.
     * @param mixed[] $inputs
     */
    private function __construct(array $inputs)
    {
        $objects = [];
        foreach ($inputs as $input){
            $objects[] = SuperString::create($input);
        }
        $this->collection = Collection::wrap($objects);
    }

    /**
     * Implements Countable interface
     * @return int
     */
    public function count()
    {
        return count($this->toArray());
    }

    /**
     * Implements JsonSerialize interface
     * @return array[]
     */
    public function jsonSerialize()
    {
        return $this->collection->jsonSerialize();
    }

    /**
     * Outputs the content of the collection as an array of strings
     * @return array[]
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * Returns true if any of the strings equals the value passed as $value
     * @param string $value
     * @return bool
     */
    public function anyElementEquals(string $value): bool
    {
        return count($this->filter(function($item) use ($value) { return $item->toString() === $value; })->toArray()) > 0;
    }

    /**
     * Returns true if any of the strings contains the value passed as $value
     * @param string $value
     * @return bool
     */
    public function anyElementContains(string $value): bool
    {
        return count($this->filter(function($item) use ($value) { return $item->contains($value); })) > 0;
    }

    /**
     * Returns true if all of the strings contain the value passed as $value
     * @param string $value
     * @return bool
     */
    public function allElementsContain(string $value): bool
    {
        return count($this->filter(function($item) use ($value) { return $item->contains($value); })) === count($this);
    }

    /**
     * Exposes map() method of Collection. Returns a new SuperStringCollection with the results of the map operation.
     * @param callable $function
     * @return SuperStringCollection
     */
    public function map(callable $function)
    {
        return new self($this->collection->map($function)->toArray());
    }

    /**
     * Exposes filter() method of Collection. Returns a new SuperStringCollection with the filtered results.
     * @param callable $function
     * @return SuperStringCollection
     */
    public function filter(callable $function)
    {
        return new self($this->collection->filter($function)->toArray());
    }

    /**
     * Sorts the collection alphabetically
     * @return SuperStringCollection
     */
    public function sort(): self
    {
        $arr = $this->toArray();
        sort($arr);
        return new self($arr);
    }

    public function join(string $separator): SuperString
    {
        return SuperString::create(implode($separator, $this->toArray()));
    }

    /**
     * Removes duplicate elements of the collection
     * @return SuperStringCollection
     * @throws \Assert\AssertionFailedException
     */
    public function unique(): SuperStringCollection
    {
        return SuperStringCollection::wrap(array_unique($this->toArray()));
    }

    /**
     * @param string $method
     * @param string[] $args
     * @return SuperStringCollection
     */
    public function __call(string $method, array $args): SuperStringCollection
    {
        $result = null;
        if (in_array($method, self::AVAILABLE_METHODS)) {
            $result = $this->collection->map(function($item) use ($method, $args) {
                /** @var callable $callback */
                $callback = [$item, $method];
                return call_user_func($callback, $args );
            });
        } else {
            throw new MethodNotFoundException('No such method', SuperStringCollection::class, $method);
        }
        return new self($result->toArray());
    }
}