<?php declare(strict_types=1);

namespace Midwinter\SuperString\tests;

use Midwinter\SuperString\SuperStringCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Exception\Doubler\MethodNotFoundException;

class SuperStringCollectionTest extends TestCase
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
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        self::assertInstanceOf( SuperStringCollection::class, $collection);
    }

    public function testAssociativeArraysLoseKeys(): void
    {
        $assoc = [
            'key' => 'value'
        ];

        $result = [
            'value'
        ];

        $collection = SuperStringCollection::wrap($assoc);
        self::assertEquals($result, $collection->toArray());
    }

    public function testToArray(): void
    {
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(self::BASIC_ARRAY, $collection->toArray());
    }

    public function testCount(): void
    {
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(3, $collection->count());
    }

    public function testAnyElementEquals(): void
    {
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        self::assertTrue($collection->anyElementEquals('foo'));
        self::assertFalse($collection->anyElementEquals('bash'));
    }

    public function testAnyElementContains(): void
    {
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
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
        $collection = SuperStringCollection::wrap($array);
        self::assertTrue($collection->allElementsContain('b'));
        self::assertFalse($collection->allElementsContain('bar'));
    }

    public function testMap(): void
    {
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(self::TO_UPPERCASE, $collection->map(function ($item) { return $item->uppercase(); })->toArray());
    }

    public function testFilter(): void
    {
        $filtered = [
            'foo',
            'bar'
        ];
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals($filtered, $collection->filter(function ($item) { return $item->toString() !== 'baz'; })->toArray());
    }

    public function testAlphabetize(): void
    {
        $alpha_order = [
            'bar',
            'baz',
            'foo'
        ];
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
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
        $collection = SuperStringCollection::wrap($duplicatesArray);
        self::assertEquals(self::BASIC_ARRAY, $collection->unique()->toArray());
    }

    public function testJoin(): void
    {
        $joined = 'foo-bar-baz';
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals($joined, $collection->join('-')->toString());
    }

    public function testMagicMethods(): void
    {
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(self::TO_UPPERCASE, $collection->uppercase()->toArray());

        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(self::BASIC_ARRAY, $collection->uppercase()->lowercase()->toArray());

        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        $this->expectException(MethodNotFoundException::class);
        $collection->missingMethod();
    }

    public function testJsonSerialize(): void
    {
        $collection = SuperStringCollection::wrap(self::BASIC_ARRAY);
        self::assertEquals(self::BASIC_ARRAY, $collection->jsonSerialize());
    }
}