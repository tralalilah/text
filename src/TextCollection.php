<?php declare(strict_types=1);

namespace TraLaLilah\Text;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Assert\InvalidArgumentException;
use Countable;
use JsonSerializable;
use Prophecy\Exception\Doubler\MethodNotFoundException;
use Tightenco\Collect\Support\Collection;

/**
 * Class TextCollection
 *
 * @package TraLaLilah\Text
 *
 * @method TextCollection first(int $chars) Returns the first $chars characters of each element
 * @method TextCollection last(int $chars) Returns the first $chars characters of each element
 * @method TextCollection leftPad(int $length, string $padding = ' ') pads each element to given length
 * @method TextCollection rightPad(int $length, string $padding = ' ') pads each element to given length
 * @method TextCollection uppercase() Converts all strings to uppercase
 * @method TextCollection lowercase() Converts all strings to lowercase
 * @method TextCollection snakeCase() Converts all strings to snake_case
 * @method TextCollection camelCase() Converts all strings to camelCase
 * @method TextCollection titleCase() Converts all strings to Title Case
 * @method TextCollection slug() Converts all strings to slugs
 * @method TextCollection replaceSpecialCharacters(string $replacement) Replace special characters
 * @method TextCollection trim() Trims all strings
 * @method TextCollection replaceAll(string $textToReplace, string $replacement) Replaces all instances in each string
 * @method TextCollection regexReplaceAll(string $replacement, string $pattern) Replaces all matches in each string
 * @method missingMethod() Does not exist. For testing purposes only.
 */
class TextCollection implements Countable, JsonSerializable
{
    private const AVAILABLE_PASS_THROUGH_METHODS = [
        'uppercase',
        'lowercase',
        'slug',
        'snakeCase',
        'camelCase',
        'titleCase',
        'replaceSpecialCharacters',
        'trim',
        'replaceAll',
        'regexReplaceAll',
        'leftPad',
        'rightPad',
        'first',
        'last'
    ];

    /**
     * @var Collection|Text[]
     */
    private $collection;

    /**
     * Returns a new instance of TextCollection.
     *
     * @param  mixed $array Contents can be of mixed types EXCEPT nested arrays.
     * Associative arrays will lose string keys.
     *
     * @return TextCollection
     * @throws AssertionFailedException
     */
    public static function wrap($array): TextCollection
    {
        Assertion::isArray($array, 'Input must be an array');
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                throw new InvalidArgumentException('Value at index ' . $key . ' must not be an array.', 400);
            }
        }
        return new self($array);
    }

    /**
     * @return TextCollection
     */
    public static function empty(): TextCollection
    {
        return new self([]);
    }

    /**
     * TextCollection constructor.
     *
     * @param mixed[] $inputs
     */
    private function __construct(array $inputs)
    {
        $objects = [];
        foreach ($inputs as $input) {
            $objects[] = Text::create($input);
        }
        $this->collection = Collection::wrap($objects);
    }

    public function add(Text $text): void
    {
        $this->collection->add($text);
    }

    /**
     * Implements Countable interface
     *
     * @return int
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * Implements JsonSerialize interface
     *
     * @return array[]
     */
    public function jsonSerialize()
    {
        return $this->collection->jsonSerialize();
    }

    /**
     * Outputs the content of the collection as an array of strings
     *
     * @return array[]
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    /**
     * Returns an array containing the length of each string
     *
     * @return int[]
     */
    public function lengths(): array
    {
        return array_map(
            function (Text $item) {
                return $item->length();
            },
            $this->collection->toArray()
        );
    }

    /**
     * Returns the length of the longest string in the collection
     *
     * @return int
     */
    public function maxLength(): int
    {
        return ($this->collection->reduce(
            function (int $current, Text $item) {
                if ($item->length() > $current) {
                      return $item->length();
                }
                return $current;
            },
            0
        ));
    }

    /**
     * Returns true if any of the strings equals the value passed as $value
     *
     * @param  string $value
     * @return bool
     */
    public function anyElementEquals(string $value): bool
    {
        return count(
            $this->filter(
                function ($item) use ($value) {
                    return $item->toString() === $value;
                }
            )->toArray()
        ) > 0;
    }

    /**
     * Returns true if any of the strings contains the value passed as $value
     *
     * @param  string $test
     * @return bool
     */
    public function anyElementContains(string $test): bool
    {
        return count(
            $this->collection->filter(
                function ($item) use ($test) {
                    return $item->contains($test);
                }
            )
        ) > 0;
    }

    /**
     * Returns true if all elements contain the value passed as $value
     *
     * @param  string $test
     * @return bool
     */
    public function allElementsContain(string $test): bool
    {
        return count(
            $this->collection->filter(
                function ($item) use ($test) {
                    return $item->contains($test);
                }
            )
        ) === count($this);
    }

    /**
     * Returns true if at least one element matches the supplied patterns
     * and false otherwise.
     *
     * @param string $pattern
     * @return bool
     */
    public function anyElementMatchesRegex(string $pattern): bool
    {
        return count(
            $this->collection->filter(
                function (Text $item) use ($pattern) {
                    return $item->matchesRegex($pattern);
                }
            )
        ) > 0;
    }

    /**
     * Returns true if all elements match the supplied pattern, and false otherwise.
     *
     * @param  string $pattern
     * @return bool
     */
    public function allElementsMatchRegex(string $pattern): bool
    {
        return count(
            $this->collection->filter(
                function (Text $item) use ($pattern) {
                    return $item->matchesRegex($pattern);
                }
            )
        ) === count($this);
    }

    /**
     * Exposes map() method of Collection. Returns a new TextCollection with the results of the map operation.
     *
     * @param  callable $function
     * @return TextCollection
     */
    public function map(callable $function)
    {
        return new self($this->collection->map($function)->toArray());
    }

    /**
     * Exposes filter() method of Collection. Returns a new TextCollection with the filtered results.
     *
     * @param  callable $function
     * @return TextCollection
     */
    public function filter(callable $function)
    {
        return new self($this->collection->filter($function)->toArray());
    }

    /**
     * Sorts the collection alphabetically
     *
     * @return TextCollection
     */
    public function sort(): TextCollection
    {
        $arr = $this->toArray();
        sort($arr);
        return new self($arr);
    }

    public function join(string $separator): Text
    {
        return Text::create(implode($separator, $this->toArray()));
    }

    /**
     * Adjusts the length of each Text to be the length of the longest, by padding right
     *
     * @return TextCollection
     */
    public function leftJustify(): TextCollection
    {
        $padLength = $this->maxLength();
        return $this->map(
            function (Text $item) use ($padLength) {
                return $item->rightPad($padLength);
            }
        );
    }

    /**
     * Adjusts the length of each Text to be the length of the longest, by padding left
     *
     * @return TextCollection
     */
    public function rightJustify(): TextCollection
    {
        $padLength = $this->maxLength();
        return $this->map(
            function (Text $item) use ($padLength) {
                return $item->leftPad($padLength);
            }
        );
    }

    /**
     * Removes duplicate elements of the collection
     *
     * @return TextCollection
     * @throws AssertionFailedException
     */
    public function unique(): TextCollection
    {
        return TextCollection::wrap(array_unique($this->toArray()));
    }

    /**
     * @param  string   $method
     * @param  string[] $args
     * @return TextCollection
     */
    public function __call(string $method, array $args): TextCollection
    {
        $result = null;
        if (in_array($method, self::AVAILABLE_PASS_THROUGH_METHODS)) {
            $result = $this->collection->map(
                function ($item) use ($method, $args) {
                    /**
                * @var callable $callback
                */
                    $callback = [$item, $method];
                    return call_user_func_array($callback, $args);
                }
            );
        } else {
            throw new MethodNotFoundException('No such method', TextCollection::class, $method);
        }
        return new self($result->toArray());
    }

    /**
     * Replaces tokens in a template with an array of replacement data,
     * returning a TextCollection object containing the merged data.
     *
     * @param  string $template
     * @param  string[] $tokens
     * @param  array[] $replaceData
     * @param  string $leftDelimiter
     * @param  string $rightDelimiter
     * @return TextCollection
     * @throws AssertionFailedException
     */
    public static function mailMerge(
        string $template,
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
            $text = Text::create($template);
            for ($i = 0; $i < $count; $i ++) {
                $textToReplace = $leftDelimiter . $tokens[$i] . $rightDelimiter;
                $text = $text->replaceAll($textToReplace, $replacementRow[$i]);
            }
            $collection->add($text);
        }
        return $collection;
    }
}
