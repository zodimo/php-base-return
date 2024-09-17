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
        $m = IOMonad::fail(10);

        $this->assertTrue($m->isFailure());
    }

    public function testFailIsValue(): void
    {
        $m = IOMonad::fail(10);

        $result = $m->unwrapFailure($this->createClosureNotCalled());
        $this->assertEquals(10, $result);
    }

    public function testFmapOnSuccess(): void
    {
        $m = IOMonad::pure(10);
        $result = $m->fmap(fn ($x) => $x + 10);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(20, $result->unwrapSuccess($this->createClosureNotCalled()));
    }

    public function testFmapOnFailure(): void
    {
        $m = IOMonad::fail(10);
        $result = $m->fmap(fn ($x) => $x + 10);
        $this->assertTrue($result->isFailure());
        $this->assertEquals(10, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testFlatmapOnSuccess(): void
    {
        $m = IOMonad::pure(10);
        $result = $m->flatMap(fn ($x) => IOMonad::pure($x + 10));
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(20, $result->unwrapSuccess($this->createClosureNotCalled()));
    }

    public function testFlatmapOnFailure(): void
    {
        $m = IOMonad::fail(10);
        $result = $m->flatMap(fn ($x) => IOMonad::pure($x + 10));
        $this->assertTrue($result->isFailure());
        $this->assertEquals(10, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testFlatmapOnSuccessWithFailure(): void
    {
        $m = IOMonad::pure(10);
        $result = $m->flatMap(fn ($x) => IOMonad::fail(100));
        $this->assertTrue($result->isFailure());
        $this->assertEquals(100, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testShouldNotComplareIOMonads(): void
    {
        $a = IOMonad::pure(10);
        $b = IOMonad::pure(11);
        $this->assertEquals($a, $b);
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
}
