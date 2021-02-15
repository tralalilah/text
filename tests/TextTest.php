<?php declare(strict_types=1);

namespace TraLaLilah\Text\tests;

use Assert\AssertionFailedException;
use Assert\InvalidArgumentException;
use TraLaLilah\Text\Text;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
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
        $text = Text::create(self::STRING);
        self::assertEquals(self::STRING, $text->toString());
    }

    public function testFromScalarNumber(): void
    {
        $text = Text::create(self::NUMBER);
        self::assertEquals((string)self::NUMBER, $text->toString());
    }

    public function testFromBooleanOrNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Text::create(true);

        $this->expectException(InvalidArgumentException::class);
        Text::create(false);

        $this->expectException(InvalidArgumentException::class);
        Text::create(null);
    }

    public function testFromObject(): void
    {
        $text = Text::create(new TestObjectToString(self::STRING));
        self::assertEquals(self::STRING, $text->toString());

        $text = Text::create(new TestObjectUnderscoreToString(self::STRING));
        self::assertEquals(self::STRING, $text->toString());

        $this->expectException(InvalidArgumentException::class);
        Text::create(new TestObjectWithNoMethods());
    }

    public function testImmutable(): void
    {
        $text = Text::create(self::STRING);
        $str2 = $text->uppercase();

        self::assertEquals(self::STRING, $text->toString());
        self::assertEquals(self::UPPER_CASE, $str2->toString());
    }

    public function testEquals(): void
    {
        $text1 = Text::create('1');
        $text1a = Text::create('1');
        $text2 = Text::create('2');

        self::assertTrue($text1->equals($text1a));
        self::assertFalse($text1->equals($text2));
    }

    public function testClone(): void
    {
        $text1 = Text::create('clone');
        $text2 = $text1->clone();

        self::assertTrue($text1->equals($text2));
        self::assertFalse($text1 === $text2);
    }

    public function testContains(): void
    {
        $text = Text::create(self::STRING);

        self::assertTrue($text->contains(self::CONTAINS_CASE_SENSE));
        self::assertFalse($text->contains(self::DOES_NOT_CONTAIN_CASE_SENSE));
        self::assertTrue($text->contains(self::CONTAINS_CASE_INSENSITIVE, false));
        self::assertFalse($text->contains(self::DOES_NOT_CONTAIN_CASE_INSENSITIVE, false));
    }

    public function testMatchesRegex(): void
    {
        $start = 'A bird is an animal.';
        $containsPattern = '/bird/';
        $doesNotContainPattern = '/tree/';
        $text = Text::create($start);

        self::assertTrue($text->matchesRegex($containsPattern));
        self::assertFalse($text->matchesRegex($doesNotContainPattern));
        $this->expectException(InvalidArgumentException::class);
        $text->matchesRegex('/broken');
    }

    public function testStartsWith(): void
    {
        $inputTrue = 'Start with some text';
        $inputFalse = 'Does not Start with';
        $inputAbsent = 'Does not';
        $test = 'Start with';

        $text = Text::create($inputTrue);
        self::assertTrue($text->startsWith($test));

        $text = Text::create($inputFalse);
        self::assertFalse($text->startsWith($test));

        $text = Text::create($inputAbsent);
        self::assertFalse($text->startsWith($test));
    }

    public function testEndsWith(): void
    {
        $inputTrue = 'This is what it ends with';
        $inputFalse = 'This is what it ends with?';
        $inputAbsent = 'This is what';
        $test = 'ends with';

        $text = Text::create($inputTrue);
        self::assertTrue($text->endsWith($test));

        $text = Text::create($inputFalse);
        self::assertFalse($text->endsWith($test));

        $text = Text::create($inputAbsent);
        self::assertFalse($text->endsWith($test));
    }

    public function testLength(): void
    {
        $text = Text::create(self::STRING);
        self::assertEquals(self::LENGTH, $text->length());
    }

    /**
     * @throws AssertionFailedException
     */
    public function testPositionOf(): void
    {
        $text = Text::create(self::STRING);
        self::assertEquals(self::SUBSTRING_POS, $text->positionOf(self::SUBSTRING));

        $this->expectException(AssertionFailedException::class);
        $text->positionOf(self::DOES_NOT_CONTAIN_CASE_SENSE);
    }

    /**
     * @throws AssertionFailedException
     */
    public function testLastPositionOf(): void
    {
        $input = 'foo foo foo';
        $search = 'foo';
        $lastPos = 8;

        $input2 = '[Here] you [are]';
        $search2 = ']';
        $lastPos2 = 15;

        $text = Text::create($input);
        self::assertEquals($lastPos, $text->lastPositionOf($search));

        $text2 = Text::create($input2);
        self::assertEquals($lastPos2, $text2->lastPositionOf($search2));
    }

    /**
     * @throws AssertionFailedException
     */
    public function testCharacterAt(): void
    {
        $string = 'ABCDE';
        $charAt4 = 'E';

        $text = Text::create($string);
        self::assertEquals($charAt4, $text->characterAt(4));
    }

    /**
     * @throws AssertionFailedException
     */
    public function testFirst(): void
    {
        $text = Text::create(self::STRING);
        self::assertEquals(self::FIRST_FIVE, $text->first(5)->toString());
        self::assertEquals(self::STRING, $text->first(1000)->toString());
        $this->expectException(AssertionFailedException::class);
        $text->first(-6);
    }

    /**
     * @throws AssertionFailedException
     */
    public function testLast(): void
    {
        $text = Text::create(self::STRING);
        self::assertEquals(self::LAST_FIVE, $text->last(5)->toString());
        self::assertEquals(self::STRING, $text->last(1000)->toString());
        $this->expectException(AssertionFailedException::class);
        $text->last(-6);
    }

    /**
     * @throws AssertionFailedException
     */
    public function testBefore(): void
    {
        $text = Text::create(self::STRING);
        self::assertEquals(self::ALL_BEFORE_THE_SUBSTRING, $text->before(self::SUBSTRING)->toString());
    }

    /**
     * @throws AssertionFailedException
     */
    public function testAfter(): void
    {
        $text = Text::create(self::STRING);
        self::assertEquals(self::ALL_AFTER_THE_SUBSTRING, $text->after(self::SUBSTRING)->toString());
    }

    /**
     * @throws AssertionFailedException
     */
    public function testAllButTheFirst(): void
    {
        $text = Text::create(self::STRING);
        self::assertEquals(self::ALL_BUT_THE_FIRST_THREE, $text->allButTheFirst(3)->toString());
    }

    public function testCount(): void
    {
        $input = 'a abc abcd';
        $check = 'a';
        $checkNone = 'e';

        $text = Text::create($input);
        self::assertEquals(3, $text->count($check));
        self::assertEquals(0, $text->count($checkNone));
    }

    /**
     * @throws AssertionFailedException
     */
    public function testBetween(): void
    {
        $inputDifferent = 'Looking for text -between? two other strings';
        $inputSame = 'Looking for text -between- two other strings';
        $inputNoBuffer = '%between%';
        $inputNoMatch = '%%';
        $inputOneToken = 'missing %one token';
        $between = 'between';

        $text = Text::create($inputDifferent);
        self::assertEquals($between, $text->between('-', '?')->toString());

        $text = Text::create($inputSame);
        self::assertEquals($between, $text->between('-', '-')->toString());

        $text = Text::create($inputNoBuffer);
        self::assertEquals($between, $text->between('%', '%')->toString());

        $text = Text::create($inputNoMatch);
        self::assertEquals('', $text->between('%', '%')->toString());

        $text = Text::create($inputOneToken);
        $this->expectException(InvalidArgumentException::class);
        $text->between('%', '%');
    }

    /**
     * @throws AssertionFailedException
     */
    public function testBetweenMoreThanOneMatch(): void
    {
        $input = '[Here] is [more] than one.';
        $text = Text::create($input);
        $response = $text->between('[', ']')->toString();
        self::assertEquals('Here', $response);
    }

    /**
     * @throws AssertionFailedException
     */
    public function testBetweenNoTokensDifferent(): void
    {
        $inputNoTokens = 'missing tokens';
        $text = Text::create($inputNoTokens);
        $this->expectException(InvalidArgumentException::class);
        $text->between('%', '?');
    }

    /**
     * @throws AssertionFailedException
     */
    public function testBetweenNoTokensSame(): void
    {
        $inputNoTokens = 'missing tokens';
        $text = Text::create($inputNoTokens);

        $this->expectException(InvalidArgumentException::class);
        $text->between('%', '%');
    }

    /**
     * @throws AssertionFailedException
     */
    public function testBetweenMany(): void
    {
        $input = '[Here] is [more] than one.';
        $expected = [
            'Here',
            'more'
        ];
        $text = Text::create($input);
        $response = $text->betweenMany('[', ']')->toArray();
        self::assertEquals($expected, $response);
    }

    /**
     * @throws AssertionFailedException
     */
    public function testBetweenManyNestedFails(): void
    {
        $input = '[Here [is] [more] than one].';
        $text = Text::create($input);
        $this->expectExceptionMessage('Nested delimiters not supported');
        $text->betweenMany('[', ']')->toArray();
    }

    /**
     * @throws AssertionFailedException
     */
    public function testAllButTheLast(): void
    {
        $text = Text::create(self::STRING);
        self::assertEquals(self::ALL_BUT_THE_LAST_THREE, $text->allButTheLast(3)->toString());
    }

    public function testConcatenate(): void
    {
        $input = 'This is ';
        $concatenate = 'a string';
        $expected = 'This is a string';
        $text = Text::create($input);
        $concatText = Text::create($concatenate);
        self::assertEquals($expected, $text->concatenate($concatText)->toString());
    }

    public function testUppercase(): void
    {
        $text = Text::create(self::STRING);
        self::assertEquals(self::UPPER_CASE, $text->uppercase()->toString());
    }

    public function testLowercase(): void
    {
        $text = Text::create(self::STRING);
        self::assertEquals(self::LOWER_CASE, $text->lowercase()->toString());
    }

    /**
     * @throws AssertionFailedException
     */
    public function testLowercaseFirst(): void
    {
        $input = 'THIS IS A STRING';
        $expected = 'tHIS IS A STRING';
        $text = Text::create($input);
        self::assertEquals($expected, $text->lowercaseFirst()->toString());
    }

    public function testUppercaseWords(): void
    {
        $input = 'these are some words';
        $expected = 'These Are Some Words';
        $text = Text::create($input);
        self::assertEquals($expected, $text->uppercaseWords()->toString());
    }

    public function testTrim(): void
    {
        $text = Text::create(self::NEEDS_TRIMMING);
        self::assertEquals(self::TRIMMED, $text->trim()->toString());
    }

    public function testLeftPad(): void
    {
        $text = 'length_is11';
        $length = 15;
        $expected = '    length_is11';
        $text = Text::create($text);
        self::assertEquals($expected, $text->leftPad($length)->toString());
    }

    public function testRightPad(): void
    {
        $text = 'length_is11';
        $length = 15;
        $expected = 'length_is11    ';
        $text = Text::create($text);
        self::assertEquals($expected, $text->rightPad($length)->toString());
    }


    /**
     * @throws AssertionFailedException
     */
    public function testReplaceOne(): void
    {
        $startString = 'foo foo bar';
        $textToReplace = 'foo';
        $replacement = 'baz';
        $result = 'baz foo bar';
        $text = Text::create($startString);
        self::assertEquals($result, $text->replaceOne($textToReplace, $replacement)->toString());
    }

    public function testReplaceAll(): void
    {
        $startString = 'foo foo bar';
        $textToReplace = 'foo';
        $replacement = 'baz';
        $result = 'baz baz bar';
        $text = Text::create($startString);
        self::assertEquals($result, $text->replaceAll($textToReplace, $replacement)->toString());
    }

    /**
     * @throws AssertionFailedException
     */
    public function testCamelCase(): void
    {
        $startString = 'This is a string';
        $camel = 'thisIsAString';
        $weird = 'This& is* a{ string ';
        $weirdCamel = 'thisIsAString';
        $text = Text::create($startString);
        self::assertEquals($camel, $text->camelCase()->toString());
        $text = Text::create($weird);
        self::assertEquals($weirdCamel, $text->camelCase()->toString());
    }

    public function testSnakeCase(): void
    {
        $startString = 'This is a string';
        $snake = 'this_is_a_string';
        $text = Text::create($startString);
        self::assertEquals($snake, $text->snakeCase()->toString());
    }

    public function testPascalCase(): void
    {
        $input = 'This is a string';
        $title = 'ThisIsAString';
        $text = Text::create($input);
        self::assertEquals($title, $text->pascalCase()->toString());
    }

    public function testSlug(): void
    {
        $input = 'This is a string';
        $expected = 'this-is-a-string';
        $text = Text::create($input);
        self::assertEquals($expected, $text->slug()->toString());
    }

    public function testReplaceSpecialCharacters(): void
    {
        $withSpecials = 'This& is* a{ string';
        $replacedWithEmptyString = 'This is a string';
        $text = Text::create($withSpecials);
        self::assertEquals($replacedWithEmptyString, $text->replaceSpecialCharacters()->toString());
    }

    public function testRegexReplaceOne(): void
    {
        $startString = 'This were a string were';
        $replaced = 'This foo a string were';
        $text = Text::create($startString);
        self::assertEquals($replaced, $text->regexReplaceOne('foo', '/were/')->toString());
    }

    public function testRegexReplaceAll(): void
    {
        $startString = 'This were a string were';
        $replaced = 'This foo a string foo';
        $text = Text::create($startString);
        self::assertEquals($replaced, $text->regexReplaceAll('foo', '/were/')->toString());
    }

    /**
     * @throws AssertionFailedException
     */
    public function testSwapText(): void
    {
        $regular = 'This/string';

        $left = 'This';
        $right = 'string';
        $swapped = 'string/This';
        $text = Text::create($regular);
        self::assertEquals($swapped, $text->swap($left, $right)->toString());

        $left = 'This';
        $right = 'string';
        $swapped = 'string/This';
        $text = Text::create($regular);
        self::assertEquals($swapped, $text->swap($right, $left)->toString());

        $regular = 'duck duck goose';
        $left = 'duck';
        $right = 'goose';
        $swapped = 'goose duck duck';
        $text = Text::create($regular);
        self::assertEquals($swapped, $text->swap($right, $left)->toString());
    }

    /**
     * @throws AssertionFailedException
     */
    public function testSplit(): void
    {
        $input = 'String A/String B/String C';
        $result = [
            'String A',
            'String B',
            'String C'
        ];
        $text = Text::create($input);
        self::assertEquals($result, $text->split('/')->toArray());
        self::assertEquals([$input], $text->split('.')->toArray());
    }
}
