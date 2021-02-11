<?php declare(strict_types=1);

namespace Midwinter\SuperString\tests;

use Assert\AssertionFailedException;
use Assert\InvalidArgumentException;
use Midwinter\SuperString\SuperString;
use PHPUnit\Framework\TestCase;

class SuperStringTest extends TestCase
{
    private const STRING = 'Super string Is a Class';
    private const NUMBER = 1.23;
    private const LENGTH = 23;
    private const LOWER_CASE = 'super string is a class';
    private const UPPER_CASE = 'SUPER STRING IS A CLASS';
    private const CONTAINS_CASE_SENSE = 'Super string';
    private const DOES_NOT_CONTAIN_CASE_SENSE = 'Super String';
    private const CONTAINS_CASE_INSENSITIVE = 'super string';
    private const DOES_NOT_CONTAIN_CASE_INSENSITIVE = 'string super';
    private const FIRST_FIVE = 'Super';
    private const LAST_FIVE = 'Class';
    private const SUBSTRING = 'Is a';
    private const SUBSTRING_POS = 13;
    private const ALL_BEFORE_THE_SUBSTRING = 'Super string ';
    private const ALL_AFTER_THE_SUBSTRING = ' Class';
    private const ALL_BUT_THE_FIRST_THREE = 'er string Is a Class';
    private const ALL_BUT_THE_LAST_THREE = 'Super string Is a Cl';
    private const NEEDS_TRIMMING = ' Class ';
    private const TRIMMED = 'Class';

    public function testFromScalarString(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::STRING, $str->toString());
    }

    public function testFromScalarNumber(): void
    {
        $str = SuperString::create(self::NUMBER);
        self::assertEquals((string)self::NUMBER, $str->toString());
    }

    public function testFromBooleanOrNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SuperString::create(TRUE);

        $this->expectException(InvalidArgumentException::class);
        SuperString::create(FALSE);

        $this->expectException(InvalidArgumentException::class);
        SuperString::create(null);
    }

    public function testFromObject(): void
    {
        $str = SuperString::create(new TestObjectToString(self::STRING));
        self::assertEquals(self::STRING, $str->toString());

        $str = SuperString::create(new TestObject__ToString(self::STRING));
        self::assertEquals(self::STRING, $str->toString());

        $this->expectException(InvalidArgumentException::class);
        SuperString::create(new TestObjectWithNoMethods());
    }

    public function testImmutable(): void
    {
        $str = SuperString::create(self::STRING);
        $str2 = $str->uppercase();
        self::assertEquals(self::STRING, $str->toString());
        self::assertEquals(self::UPPER_CASE, $str2->toString());
    }

    public function testContains(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertTrue($str->contains(self::CONTAINS_CASE_SENSE));
        self::assertFalse($str->contains(self::DOES_NOT_CONTAIN_CASE_SENSE));
        self::assertTrue($str->contains(self::CONTAINS_CASE_INSENSITIVE, false));
        self::assertFalse($str->contains(self::DOES_NOT_CONTAIN_CASE_INSENSITIVE, false));
    }

    public function testContainsRegex(): void
    {
        $start = 'A bird is an animal.';
        $containsPattern = '/bird/';
        $doesNotContainPattern = '/tree/';
        $str = SuperString::create($start);
        self::assertTrue($str->containsRegex($containsPattern));
        self::assertFalse($str->containsRegex($doesNotContainPattern));
        $this->expectException(InvalidArgumentException::class);
        $str->containsRegex('/broken');
    }

    public function testLength(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::LENGTH, $str->length());
    }

    public function testPositionOf(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::SUBSTRING_POS, $str->positionOf(self::SUBSTRING));

        $this->expectException(AssertionFailedException::class);
        $str->positionOf(self::DOES_NOT_CONTAIN_CASE_SENSE);
    }

    public function testCharacterAt(): void
    {
        $string = 'ABCDE';
        $charAt4 = 'E';
        $str = SuperString::create($string);
        self::assertEquals($charAt4, $str->characterAt(4));
    }

    public function testFirst(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::FIRST_FIVE, $str->first(5)->toString());
        self::assertEquals(self::STRING, $str->first(1000)->toString());
        $this->expectException(AssertionFailedException::class);
        $str->first(-6);
    }

    public function testLast(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::LAST_FIVE, $str->last(5)->toString());
        self::assertEquals(self::STRING, $str->last(1000)->toString());
        $this->expectException(AssertionFailedException::class);
        $str->last(-6);
    }

    public function testBefore(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::ALL_BEFORE_THE_SUBSTRING, $str->before(self::SUBSTRING)->toString());
    }

    public function testAfter(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::ALL_AFTER_THE_SUBSTRING, $str->after(self::SUBSTRING)->toString());
    }

    public function testAllButTheFirst(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::ALL_BUT_THE_FIRST_THREE, $str->allButTheFirst(3)->toString());
    }

    public function testAllButTheLast(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::ALL_BUT_THE_LAST_THREE, $str->allButTheLast(3)->toString());
    }

    public function testUppercase(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::UPPER_CASE, $str->uppercase()->toString());
    }

    public function testLowercase(): void
    {
        $str = SuperString::create(self::STRING);
        self::assertEquals(self::LOWER_CASE, $str->lowercase()->toString());
    }

    public function testTrim(): void
    {
        $str = SuperString::create(self::NEEDS_TRIMMING);
        self::assertEquals(self::TRIMMED, $str->trim()->toString());
    }

    public function testReplaceOne(): void
    {
        $startString = 'foo foo bar';
        $textToReplace = 'foo';
        $replacement = 'baz';
        $result = 'baz foo bar';
        $str = SuperString::create($startString);
        self::assertEquals($result, $str->replaceOne($textToReplace, $replacement)->toString());
    }

    public function testReplaceAll(): void
    {
        $startString = 'foo foo bar';
        $textToReplace = 'foo';
        $replacement = 'baz';
        $result = 'baz baz bar';
        $str = SuperString::create($startString);
        self::assertEquals($result, $str->replaceAll($textToReplace, $replacement)->toString());
    }

    public function testCamelCase(): void
    {
        $startString = 'This is a string';
        $camel = 'thisIsAString';
        $weird = 'This& is* a{ string ';
        $weirdCamel = 'this_Is_A_String';
        $str = SuperString::create($startString);
        self::assertEquals($camel, $str->camelCase()->toString());
        $str = SuperString::create($weird);
        self::assertEquals($weirdCamel, $str->camelCase()->toString());
    }

    public function testSnakeCase(): void
    {
        $startString = 'This is a string';
        $snake = 'this_is_a_string';
        $str = SuperString::create($startString);
        self::assertEquals($snake, $str->snakeCase()->toString());
    }

    public function testTitleCase(): void
    {
        $startString = 'This is a string';
        $title = 'This Is A String';
        $str = SuperString::create($startString);
        self::assertEquals($title, $str->titleCase()->toString());
    }

    public function testReplaceSpecialCharacters(): void
    {
        $withSpecials = 'This& is* a{ string';
        $replacedWithEmptyString = 'This is a string';
        $str = SuperString::create($withSpecials);
        self::assertEquals($replacedWithEmptyString, $str->replaceSpecialCharacters('')->toString());
    }

    public function testRegexReplaceOne(): void
    {
        $startString = 'This were a string were';
        $replaced = 'This foo a string were';
        $str = SuperString::create($startString);
        self::assertEquals($replaced, $str->regexReplaceOne('foo', '/were/')->toString());
    }

    public function testRegexReplaceAll(): void
    {
        $startString = 'This were a string were';
        $replaced = 'This foo a string foo';
        $str = SuperString::create($startString);
        self::assertEquals($replaced, $str->regexReplaceAll('foo', '/were/')->toString());
    }

    public function testSwapText(): void
    {
        $regular = 'This/string';

        $left = 'This';
        $right = 'string';
        $swapped = 'string/This';
        $str = SuperString::create($regular);
        self::assertEquals($swapped, $str->swapText($left, $right)->toString());

        $left = 'This';
        $right = 'string';
        $swapped = 'string/This';
        $str = SuperString::create($regular);
        self::assertEquals($swapped, $str->swapText($right, $left)->toString());

        $regular = 'duck duck goose';
        $left = 'duck';
        $right = 'goose';
        $swapped = 'goose duck duck';
        $str = SuperString::create($regular);
        self::assertEquals($swapped, $str->swapText($right, $left)->toString());
    }

    public function testSplit(): void
    {
        $input = 'String A/String B/String C';
        $result = [
            'String A',
            'String B',
            'String C'
        ];
        $str = SuperString::create($input);
        self::assertEquals($result, $str->split('/')->toArray());
        self::assertEquals([$input], $str->split('.')->toArray());
    }
}

class TestObjectToString
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}

class TestObject__ToString
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

class TestObjectWithNoMethods {}
