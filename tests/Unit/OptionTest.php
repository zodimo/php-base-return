<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Option;

/**
 * @internal
 *
 * @coversNothing
 */
class OptionTest extends TestCase
{
    /**
     * Constructors.
     */
    public function testSome(): void
    {
        $option = Option::some(10);
        $this->assertInstanceOf(Option::class, $option);
    }

    public function testNone(): void
    {
        $option = Option::none();
        $this->assertInstanceOf(Option::class, $option);
    }

    /**
     * isSome.
     */
    public function testSomeIsSome(): void
    {
        $option = Option::some(10);
        $this->assertTrue($option->isSome());
        $this->assertFalse($option->isNone());
    }

    /**
     * isNone.
     */
    public function testNoneIsNone(): void
    {
        $option = Option::none();
        $this->assertTrue($option->isNone());
        $this->assertFalse($option->isSome());
    }

    /**
     * unwrap.
     */
    public function testSomeUnwrapNotCallOnNoneCallback(): void
    {
        $option = Option::some(10);
        $this->assertEquals(10, $option->unwrap(fn () => 'none'));
    }

    public function testNoneUnwrapCallsOnNoneCallback(): void
    {
        $option = Option::none();
        $this->assertEquals('none', $option->unwrap(fn () => 'none'));
    }

    /**
     * match.
     */
    public function testMatchOnSome(): void
    {
        $result = Option::some(10)->match(
            fn (int $n) => $n + 10,
            fn () => 'none'
        );
        $this->assertEquals(20, $result);
    }

    public function testMatchOnNone(): void
    {
        $result = Option::none()->match(
            fn ($_) => 'some',
            fn () => 'none'
        );
        $this->assertEquals('none', $result);
    }

    /**
     * map.
     */
    public function testMapOnSome(): void
    {
        $result = Option::some(10)->map(fn (int $x) => $x + 10);
        $this->assertEquals(20, $result->unwrap(fn () => 'none'));
    }

    public function testMapOnNone(): void
    {
        $result = Option::none()->map(fn (int $x) => $x + 10);
        $this->assertEquals('none', $result->unwrap(fn () => 'none'));
    }

    /**
     * flatmap.
     */
    public function testFlatmapOnSomeWithSome(): void
    {
        $result = Option::some(10)->flatMap(fn (int $x) => Option::some($x + 10));
        $this->assertEquals(20, $result->unwrap(fn () => 'none'));
    }

    public function testFlatmapOnSomeWithNone(): void
    {
        $result = Option::some(10)->flatMap(fn ($_) => Option::none());
        $this->assertEquals('none', $result->unwrap(fn () => 'none'));
    }

    public function testFlatmapOnNone(): void
    {
        $result = Option::none()->flatMap(fn ($_) => Option::some('some'));
        $this->assertEquals('none', $result->unwrap(fn () => 'none'));
    }
}
