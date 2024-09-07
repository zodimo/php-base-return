<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Option;
use Zodimo\BaseReturn\Tests\MockClosureTrait;

/**
 * @internal
 *
 * @coversNothing
 */
class OptionTest extends TestCase
{
    use MockClosureTrait;

    /**
     * Constructors.
     */
    public function testCanCreateSome(): void
    {
        $option = Option::some(10);
        $this->assertInstanceOf(Option::class, $option);
    }

    public function testCanCreateNone(): void
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
    public function testUnwrapOnSome(): void
    {
        $option = Option::some(10);
        $onNone = $this->createClosureMock();
        $onNone->expects($this->never())->method('__invoke');
        $this->assertEquals(10, $option->unwrap($onNone));
    }

    public function testUnwrapOnNone(): void
    {
        $option = Option::none();
        $onNone = $this->createClosureMock();
        $onNone->expects($this->once())->method('__invoke')->willReturn('none');
        $this->assertEquals('none', $option->unwrap($onNone));
    }

    /**
     * match.
     */
    public function testMatchOnSome(): void
    {
        $onNone = $this->createClosureMock();
        $onNone->expects($this->never())->method('__invoke');

        $result = Option::some(10)->match(
            fn (int $n) => $n + 10,
            $onNone
        );
        $this->assertEquals(20, $result);
    }

    public function testMatchOnNone(): void
    {
        $onSome = $this->createClosureMock();
        $onSome->expects($this->never())->method('__invoke');
        $result = Option::none()->match(
            $onSome,
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

        $onNone = $this->createClosureMock();
        $onNone->expects($this->never())->method('__invoke');

        $this->assertEquals(20, $result->unwrap($onNone));
    }

    public function testMapOnNone(): void
    {
        $result = Option::none()->map(fn (int $x) => $x + 10);
        $onNone = $this->createClosureMock();
        $onNone->expects($this->once())->method('__invoke')->willReturn('none');

        $this->assertEquals('none', $result->unwrap($onNone));
    }

    /**
     * flatmap.
     */
    public function testFlatmapOnSomeWithSome(): void
    {
        $onNone = $this->createClosureMock();
        $onNone->expects($this->never())->method('__invoke');

        $result = Option::some(10)->flatMap(fn (int $x) => Option::some($x + 10));

        $this->assertEquals(20, $result->unwrap($onNone));
    }

    public function testFlatmapOnSomeWithNone(): void
    {
        $onNone = $this->createClosureMock();
        $onNone->expects($this->once())->method('__invoke')->willReturn('none');

        $result = Option::some(10)->flatMap(fn ($_) => Option::none());
        $this->assertEquals('none', $result->unwrap($onNone));
    }

    public function testFlatmapOnNone(): void
    {
        $func = $this->createClosureMock();
        $func->expects($this->never())->method('__invoke');

        $onNone = $this->createClosureMock();
        $onNone->expects($this->once())->method('__invoke')->willReturn('none');

        /**
         * @var callable(mixed):Option<mixed> $func
         */
        $result = Option::none()->flatMap($func);
        $this->assertEquals('none', $result->unwrap($onNone));
    }
}
