<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Either;
use Zodimo\BaseReturn\Option;
use Zodimo\BaseReturnTest\MockClosureTrait;

/**
 * @internal
 *
 * @coversNothing
 */
class EitherTest extends TestCase
{
    use MockClosureTrait;

    /**
     * Constructors.
     */
    public function testLeft(): void
    {
        $either = Either::left(10);

        $this->assertInstanceOf(Either::class, $either);
    }

    public function testRight(): void
    {
        $either = Either::right(10);

        $this->assertInstanceOf(Either::class, $either);
    }

    public function testLeftIsLeft(): void
    {
        $either = Either::left(10);
        $this->assertTrue($either->isLeft());
        // confirming the seemingly obvious
        // @phpstan-ignore method.impossibleType
        $this->assertFalse($either->isRight());
    }

    public function testRightIsRight(): void
    {
        $either = Either::right(10);
        $this->assertTrue($either->isRight());
        // confirming the seemingly obvious
        // @phpstan-ignore method.impossibleType
        $this->assertFalse($either->isLeft());
    }

    /**
     * UnwrapLeft.
     */
    public function testUnwrapLeftOnLeft(): void
    {
        $either = Either::left(10);

        $onRight = $this->createClosureMock();
        $onRight->expects($this->never())->method('__invoke');

        $result = $either->unwrapLeft($onRight);
        $this->assertEquals(10, $result);
    }

    public function testUnwrapLeftOnRight(): void
    {
        $either = Either::right(10);

        $onRight = $this->createClosureMock();
        $onRight->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $result = $either->unwrapLeft($onRight);
        $this->assertEquals(20, $result);
    }

    /**
     * unwrapRight.
     */
    public function testUnwrapRightOnRight(): void
    {
        $either = Either::right(10);

        $onLeft = $this->createClosureMock();
        $onLeft->expects($this->never())->method('__invoke');

        $result = $either->unwrapRight($onLeft);
        $this->assertEquals(10, $result);
    }

    public function testUnwrapRightOnLeft(): void
    {
        $either = Either::left(10);

        $onLeft = $this->createClosureMock();
        $onLeft->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $result = $either->unwrapRight($onLeft);
        $this->assertEquals(20, $result);
    }

    /**
     * matchLeft.
     */
    public function testMatchLeftOnLeft(): void
    {
        $either = Either::left(10);

        $onLeft = $this->createClosureMock();
        $onLeft->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $result = $either->matchLeft($onLeft);
        $this->assertInstanceOf(Option::class, $result);
        $this->assertEquals(Option::some(20), $result);
    }

    public function testMatchLeftOnRight(): void
    {
        $either = Either::right(10);

        $onLeft = $this->createClosureMock();
        $onLeft->expects($this->never())->method('__invoke');

        $result = $either->matchLeft($onLeft);
        $this->assertInstanceOf(Option::class, $result);
        $this->assertEquals(Option::none(), $result);
    }

    /**
     * matchRight.
     */
    public function testMatchRightOnRight(): void
    {
        $either = Either::right(10);

        $onRight = $this->createClosureMock();
        $onRight->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $result = $either->matchRight($onRight);
        $this->assertInstanceOf(Option::class, $result);
        $this->assertEquals(Option::some(20), $result);
    }

    public function testMatchRightOnLeft(): void
    {
        $either = Either::left(10);

        $onRight = $this->createClosureMock();
        $onRight->expects($this->never())->method('__invoke');

        $result = $either->matchRight($onRight);
        $this->assertInstanceOf(Option::class, $result);
        $this->assertEquals(Option::none(), $result);
    }

    /**
     * match.
     */
    public function testMatchOnLeft(): void
    {
        $either = Either::left(10);

        $onLeft = $this->createClosureMock();
        $onLeft->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $onRight = $this->createClosureMock();
        $onRight->expects($this->never())->method('__invoke');

        $result = $either->match($onLeft, $onRight);
        $this->assertEquals(20, $result);
    }

    public function testMatchOnRight(): void
    {
        $either = Either::right(10);

        $onLeft = $this->createClosureMock();
        $onLeft->expects($this->never())->method('__invoke');

        $onRight = $this->createClosureMock();
        $onRight->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $result = $either->match($onLeft, $onRight);
        $this->assertEquals(20, $result);
    }

    /**
     * mapLeft.
     */
    public function testMapLeftOnLeft(): void
    {
        $either = Either::left(10);
        $onLeft = $this->createClosureMock();
        $onLeft->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $result = $either->mapLeft($onLeft);
        $this->assertEquals(Either::left(20), $result);
    }

    public function testMapLeftOnRight(): void
    {
        $either = Either::right(10);
        $onLeft = $this->createClosureMock();
        $onLeft->expects($this->never())->method('__invoke');

        $result = $either->mapLeft($onLeft);
        $this->assertSame($either, $result);
    }

    /**
     * mapRight.
     */
    public function testMapRightOnLeft(): void
    {
        $either = Either::left(10);

        $onRight = $this->createClosureMock();
        $onRight->expects($this->never())->method('__invoke');

        $result = $either->mapRight($onRight);
        $this->assertSame($either, $result);
    }

    public function testMapRightOnRight(): void
    {
        $either = Either::right(10);

        $onRight = $this->createClosureMock();
        $onRight->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $result = $either->mapRight($onRight);
        $this->assertEquals(Either::right(20), $result);
    }

    public function testMapBothOnLeft(): void
    {
        $either = Either::left(10);

        $onLeft = $this->createClosureMock();
        $onLeft->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $onRight = $this->createClosureMock();
        $onRight->expects($this->never())->method('__invoke');

        $result = $either->mapBoth($onLeft, $onRight);
        $this->assertEquals(Either::left(20), $result);
    }

    public function testMapBothOnRight(): void
    {
        $either = Either::right(10);

        $onLeft = $this->createClosureMock();
        $onLeft->expects($this->never())->method('__invoke');

        $onRight = $this->createClosureMock();
        $onRight->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $result = $either->mapBoth($onLeft, $onRight);
        $this->assertEquals(Either::right(20), $result);
    }

    /**
     * flatmap on left.
     */
    public function testFlatMapOnLeft(): void
    {
        $either = Either::left(10);

        $func = $this->createClosureMock();
        $func->expects($this->never())->method('__invoke');

        /**
         * @var callable(int):Either<mixed, int> $func
         */
        $result = $either->flatMap($func);
        $this->assertSame($either, $result);
    }

    public function testFlatMapOnRight(): void
    {
        $either = Either::right(10);

        $func = $this->createClosureMock();

        $func->expects($this->once())->method('__invoke')->with(10)->willReturn(Either::right(20));

        /**
         * @var callable(int):Either<mixed, int> $func
         */
        $result = $either->flatMap($func);
        $this->assertEquals(Either::right(20), $result);
    }
}
