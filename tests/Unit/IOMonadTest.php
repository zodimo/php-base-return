<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturnTest\MockClosureTrait;

/**
 * @internal
 *
 * @coversNothing
 */
class IOMonadTest extends TestCase
{
    use MockClosureTrait;

    public function testCanCreate(): void
    {
        $m = IOMonad::pure(10);
        $this->assertInstanceOf(IOMonad::class, $m);
    }

    public function testPureIsSuccess(): void
    {
        $m = IOMonad::pure(10);
        $result = $m->isSuccess();
        $this->assertTrue($result);
    }

    public function testPureValue(): void
    {
        $m = IOMonad::pure(10);
        $result = $m->unwrapSuccess($this->createClosureNotCalled());
        $this->assertEquals(10, $result);
    }

    public function testFailIsFailure(): void
    {
        $error = new \RuntimeException('error');
        $m = IOMonad::fail($error);

        $this->assertTrue($m->isFailure());
    }

    public function testFailIsValue(): void
    {
        $m = IOMonad::fail(10);

        $result = $m->unwrapFailure($this->createClosureNotCalled());
        $this->assertSame(10, $result);
    }

    // ///////////////
    //  fmap
    // //////////////

    public function testFmapOnSuccess(): void
    {
        $m = IOMonad::pure(10);

        $fmapFn = $this->createClosureMock();
        $fmapFn->expects($this->once())->method('__invoke')->with(10)->willReturn(20);

        $result = $m->fmap($fmapFn);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(20, $result->unwrapSuccess($this->createClosureNotCalled()));
    }

    public function testFmapOnFailure(): void
    {
        $error = new \InvalidArgumentException('Failed');
        $m = IOMonad::fail($error);
        $fmapMockClosure = $this->createClosureNotCalled();
        $result = $m->fmap($fmapMockClosure);
        $this->assertTrue($result->isFailure());

        $this->assertSame($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testFlatmapOnSuccess(): void
    {
        $m = IOMonad::pure(10);

        $flatmapFn = $this->createClosureMock();
        $flatmapFn->expects($this->once())->method('__invoke')->with(10)->willReturn(IOMonad::pure(20));

        /**
         * helping phpstan.
         *
         * @var callable(int):IOMonad<int,mixed> $flatmapFn
         */
        $result = $m->flatMap($flatmapFn);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(20, $result->unwrapSuccess($this->createClosureNotCalled()));
    }

    public function testFlatmapOnFailure(): void
    {
        $error = new \InvalidArgumentException('Failed');
        $m = IOMonad::fail($error);

        /**
         * helping phpstan.
         *
         * @var callable(mixed):IOMonad<mixed,\Throwable> $flatmapFn
         */
        $flatmapFn = $this->createClosureNotCalled();
        $result = $m->flatMap($flatmapFn);
        $this->assertTrue($result->isFailure());
        $this->assertSame($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testFlatmapOnSuccessWithFailure(): void
    {
        $error = new \InvalidArgumentException('Failed');
        $m = IOMonad::pure(10);

        $flatmapFn = $this->createClosureMock();
        $flatmapFn->expects($this->once())->method('__invoke')->with(10)->willReturn(IOMonad::fail($error));

        /**
         * helping phpstan.
         *
         * @var callable(mixed):IOMonad<int,\Throwable> $flatmapFn
         */
        $result = $m->flatMap($flatmapFn);

        $this->assertTrue($result->isFailure());
        $this->assertSame($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testCanComplareIOMonads(): void
    {
        $a = IOMonad::pure(10);
        $b = IOMonad::pure(11);
        $aa = IOMonad::pure(10);
        $this->assertTrue($a !== $b);
        $this->assertTrue($a == $aa);
    }

    public function testStackSafeFlatMap(): void
    {
        $m = IOMonad::pure(0);
        $f = fn (int $x) => IOMonad::pure($x + 1);

        foreach (range(0, 4999) as $_) {
            $m = $m->flatMap($f);
        }
        $this->assertEquals(5000, $m->unwrapSuccess($this->createClosureNotCalled()));
    }

    public function testTryWithSuccess(): void
    {
        $func = fn () => 10;
        $m = IOMonad::try($func);

        $this->assertEquals(10, $m->unwrapSuccess($this->createClosureNotCalled()));
    }

    public function testTryWithFailure(): void
    {
        $exception = new \RuntimeException('failed');
        $func = function () use ($exception) {
            throw $exception;
        };
        $m = IOMonad::try($func);

        $this->assertSame($exception, $m->unwrapFailure($this->createClosureNotCalled()));
    }

    // ///////////////
    //  tapSuccess.
    // //////////////
    public function testTapSuccessOnSuccessWithSuccess(): void
    {
        $m = IOMonad::pure(10);

        $tapFn = $this->createClosureMock();
        $tapFn->expects($this->once())->method('__invoke')->with(10)->willReturn(IOMonad::pure(null));

        $result = $m->tapSuccess($tapFn);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(10, $result->unwrapSuccess($this->createClosureNotCalled()));
    }

    public function testTapSuccessOnSuccessWithFailure(): void
    {
        $error = new \InvalidArgumentException('Failed');
        $m = IOMonad::pure(10);

        $tapFn = $this->createClosureMock();
        $tapFn->expects($this->once())->method('__invoke')->with(10)->willReturn(IOMonad::fail($error));

        $result = $m->tapSuccess($tapFn);

        $this->assertTrue($result->isFailure());
        $this->assertSame($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testTapSuccessOnFailure(): void
    {
        $error = new \InvalidArgumentException('Failed');
        $m = IOMonad::fail($error);

        /**
         * helping phpstan.
         *
         * @var callable(mixed):IOMonad<mixed,\Throwable> $flatmapFn
         */
        $flatmapFn = $this->createClosureNotCalled();
        $result = $m->tapSuccess($flatmapFn);
        $this->assertTrue($result->isFailure());
        $this->assertSame($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    // ///////////////
    //  tapFailure.
    // //////////////

    public function testTapFailureOnFailuresWithSuccess(): void
    {
        $error = new \InvalidArgumentException('Failed');
        $m = IOMonad::fail($error);
        $tapFn = $this->createClosureMock();
        $tapFn->expects($this->once())->method('__invoke')->with($error)->willReturn(IOMonad::pure(null));

        /**
         * helping phpstan.
         *
         * @var callable(mixed):IOMonad<mixed,\Throwable> $tapFn
         */
        $result = $m->tapFailure($tapFn);
        $this->assertTrue($result->isFailure());
        $this->assertSame($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testTapFailureOnFailuresWithFailure(): void
    {
        $error = new \InvalidArgumentException('Failed');
        $tapError = new \RuntimeException('Failed');
        $m = IOMonad::fail($error);

        $tapFn = $this->createClosureMock();
        $tapFn->expects($this->once())->method('__invoke')->with($error)->willReturn(IOMonad::fail($tapError));

        /**
         * helping phpstan.
         *
         * @var callable(mixed):IOMonad<mixed,\Throwable> $tapFn
         */
        $result = $m->tapFailure($tapFn);
        $this->assertTrue($result->isFailure());
        $this->assertSame($tapError, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testTapFailureOnSuccess(): void
    {
        $m = IOMonad::pure(10);

        /**
         * helping phpstan.
         *
         * @var callable(mixed):IOMonad<mixed,\Throwable> $flatmapFn
         */
        $flatmapFn = $this->createClosureNotCalled();
        $result = $m->tapFailure($flatmapFn);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(10, $result->unwrapSuccess($this->createClosureNotCalled()));
    }

    // ///////////////
    //  fmapFailure.
    // //////////////

    public function testFmapFailureOnSuccess(): void
    {
        $m = IOMonad::pure(10);
        $fmapMockClosure = $this->createClosureNotCalled();
        $result = $m->fmapFailure($fmapMockClosure);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(10, $result->unwrapSuccess($this->createClosureNotCalled()));
    }

    public function testFmapFailureOnFailure(): void
    {
        $error = new \InvalidArgumentException('Failed');
        $error2 = new \RuntimeException('Failed');
        $m = IOMonad::fail($error);

        $fmapClosure = $this->createClosureMock();
        $fmapClosure->expects($this->once())->method('__invoke')->with($error)->willReturn($error2);

        $result = $m->fmapFailure($fmapClosure);
        $this->assertTrue($result->isFailure());

        $this->assertSame($error2, $result->unwrapFailure($this->createClosureNotCalled()));
    }
}
