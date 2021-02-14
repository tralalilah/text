<?php declare(strict_types=1);

namespace TraLaLilah\Text\tests;

use TraLaLilah\Text\Text;
use TraLaLilah\Text\TextCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Exception\Doubler\MethodNotFoundException;

class TextCollectionTest extends TestCase
{
    private const BASIC_ARRAY = [
            'foo',
            'bar',
            'baz'
        ];

    private const TO_UPPERCASE = [
            'FOO',
            'BAR',
            'BAZ'
        ];

    public function testCreate(): void
    {
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        self::assertInstanceOf(TextCollection::class, $collection);
    }

    public function testAssociativeArraysLoseKeys(): void
    {
        $assoc = [
            'key' => 'value'
        ];

        $result = [
            'value'
        ];

        $collection = TextCollection::wrap($assoc);
        self::assertEquals($result, $collection->toArray());
    }

    public function testToArray(): void
    {
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(self::BASIC_ARRAY, $collection->toArray());
    }

    public function testAdd(): void
    {
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        $collection->add(Text::create('bash'));
        self::assertCount(4, $collection);
    }

    public function testCount(): void
    {
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(3, $collection->count());
    }

    public function testLengths(): void
    {
        $variousLengths = [
            '1',
            '12345678',
            '123'
        ];
        $expected = [
            1,
            8,
            3
        ];
        $collection = TextCollection::wrap($variousLengths);
        self::assertEquals($expected, $collection->lengths());
    }

    public function testMaxLength(): void
    {
        $variousLengths = [
            '1',
            '12345678',
            '123'
        ];
        $collection = TextCollection::wrap($variousLengths);
        self::assertEquals(8, $collection->maxLength());
    }

    public function testAnyElementEquals(): void
    {
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        self::assertTrue($collection->anyElementEquals('foo'));
        self::assertFalse($collection->anyElementEquals('bash'));
    }

    public function testAnyElementContains(): void
    {
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        self::assertTrue($collection->anyElementContains('fo'));
        self::assertFalse($collection->anyElementContains('foooo'));
    }

    public function testAllElementsContain(): void
    {
        $array = [
            'bar',
            'baz',
            'bash'
        ];
        $collection = TextCollection::wrap($array);
        self::assertTrue($collection->allElementsContain('b'));
        self::assertFalse($collection->allElementsContain('bar'));
    }

    public function testMap(): void
    {
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(
            self::TO_UPPERCASE, $collection->map(
                function ($item) {
                    return $item->uppercase(); 
                }
            )->toArray()
        );
    }

    public function testFilter(): void
    {
        $filtered = [
            'foo',
            'bar'
        ];
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(
            $filtered, $collection->filter(
                function ($item) {
                    return $item->toString() !== 'baz'; 
                }
            )->toArray()
        );
    }

    public function testAlphabetize(): void
    {
        $alpha_order = [
            'bar',
            'baz',
            'foo'
        ];
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals($alpha_order, $collection->sort()->toArray());
    }

    public function testUnique(): void
    {
        $duplicatesArray = [
            'foo',
            'bar',
            'baz',
            'foo',
            'bar'
        ];
        $collection = TextCollection::wrap($duplicatesArray);
        self::assertEquals(self::BASIC_ARRAY, $collection->unique()->toArray());
    }

    public function testJoin(): void
    {
        $joined = 'foo-bar-baz';
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals($joined, $collection->join('-')->toString());
    }

    public function testLeftJustify(): void
    {
        $input = [
            '1',
            '22',
            '55555'
        ];

        $expected = [
            '1    ',
            '22   ',
            '55555'
        ];
        $collection = TextCollection::wrap($input);
        self::assertEquals($expected, $collection->leftJustify()->toArray());
    }

    public function testRightJustify(): void
    {
        $input = [
            '1',
            '22',
            '55555'
        ];

        $expected = [
            '    1',
            '   22',
            '55555'
        ];
        $collection = TextCollection::wrap($input);
        self::assertEquals($expected, $collection->rightJustify()->toArray());
    }

    public function testMagicMethods(): void
    {
        $input = [
            'This is a string',
            'THIS IS A STRING',
            'this is a string'
            ];
        $allUppercase = [
            'THIS IS A STRING',
            'THIS IS A STRING',
            'THIS IS A STRING'
        ];
        $allLowercase = [
            'this is a string',
            'this is a string',
            'this is a string'
        ];
        $allSlugs = [
            'this-is-a-string',
            'this-is-a-string',
            'this-is-a-string'
        ];

        $collection = TextCollection::wrap($input);
        self::assertEquals($allUppercase, $collection->uppercase()->toArray());

        $collection = TextCollection::wrap($input);
        self::assertEquals($allLowercase, $collection->lowercase()->toArray());

        $collection = TextCollection::wrap($input);
        self::assertEquals($allSlugs, $collection->slug()->toArray());

        $collection = TextCollection::wrap($input);
        $this->expectException(MethodNotFoundException::class);
        $collection->missingMethod();
    }

    public function testJsonSerialize(): void
    {
        $collection = TextCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(self::BASIC_ARRAY, $collection->jsonSerialize());
    }
}