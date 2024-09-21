<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Option;
use Zodimo\BaseReturn\Result;
use Zodimo\BaseReturnTest\MockClosureTrait;

/**
 * @internal
 *
 * @coversNothing
 */
class ResultTest extends TestCase
{
    use MockClosureTrait;

    /**
     * Constructors.
     */
    public function testSucceed(): void
    {
        $result = Result::succeed(10);
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testFail(): void
    {
        $error = new \RuntimeException('error');
        $result = Result::fail($error);
        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * isSuccess.
     */
    public function testSucceedIsSuccess(): void
    {
        $result = Result::succeed(10);
        $this->assertTrue($result->isSuccess());
        // confirming the seemingly obvious
        // @phpstan-ignore method.impossibleType
        $this->assertFalse($result->isFailure());
    }

    /**
     * isFailure.
     */
    public function testFailIsFailure(): void
    {
        $error = new \RuntimeException('error');
        $result = Result::fail($error);
        $this->assertTrue($result->isFailure());
        // confirming the seemingly obvious
        // @phpstan-ignore method.impossibleType
        $this->assertFalse($result->isSuccess());
    }

    /**
     * success.
     */
    public function testSucceedSuccessOptionSome(): void
    {
        $result = Result::succeed(10);
        $option = $result->success();
        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isSome());
        $this->assertEquals(10, $option->unwrap($this->createClosureNotCalled()));
    }

    public function testFailSuccessOptionNone(): void
    {
        $error = new \RuntimeException('error');
        $result = Result::fail($error);
        $option = $result->success();
        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isNone());
    }

    /**
     * failure.
     */
    public function testFailFailureOptionSome(): void
    {
        $error = new \RuntimeException('error');
        $result = Result::fail($error);
        $option = $result->failure();
        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isSome());
        $this->assertEquals($error, $option->unwrap($this->createClosureNotCalled()));
    }

    public function testSucceedFailureOptionNone(): void
    {
        $result = Result::succeed(10);
        $option = $result->failure();
        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isNone());
    }

    /**
     * unwrap.
     */
    public function testSucceedUnwrapNotCallOnFailureCallback(): void
    {
        $result = Result::succeed(10);
        $this->assertEquals(10, $result->unwrap(fn ($_) => 0));
    }

    public function testFailureUnwrapCallsOnFailureCallback(): void
    {
        $error = new \RuntimeException('error');
        $result = Result::fail($error);

        $this->assertEquals(10, $result->unwrap(fn ($_) => 10));
    }

    /**
     * unwrapFailure.
     */
    public function testFailureUnwrapFailureNotCallOnSuccessCallback(): void
    {
        $error = new \RuntimeException('error');
        $result = Result::fail($error);

        $this->assertEquals($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testFailureUnwrapFailurCallsOnSuccessCallback(): void
    {
        $result = Result::succeed('success');
        $falseNegativeError = new \RuntimeException('false negative');
        $this->assertSame($falseNegativeError, $result->unwrapFailure(fn ($_) => $falseNegativeError));
    }

    /**
     * match.
     */
    public function testMatchOnSuccess(): void
    {
        $result = Result::succeed(10)->match(
            fn ($value) => $value + 10,
            fn ($_) => 0
        );
        $this->assertEquals(20, $result);
    }

    public function testMatchOnFailure(): void
    {
        $error = new \RuntimeException('error');

        $result = Result::fail($error)->match(
            fn ($_) => 10,
            fn ($e) => 0
        );
        $this->assertEquals(0, $result);
    }

    /**
     * fromOption constructor.
     */
    public function testFromOpionSomeIsSuccess(): void
    {
        $option = Option::some(10);

        /**
         * helping phpstan.
         *
         * @var callable():\Throwable
         */
        $mockClosureNotCalled = $this->createClosureNotCalled();

        $result = Result::fromOption($option, $mockClosureNotCalled);
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(10, $result->unwrap(fn ($_) => 0));
    }

    public function testFromOptionNoneIsFailure(): void
    {
        $option = Option::none();
        $error = new \RuntimeException('nothing');
        $result = Result::fromOption($option, fn () => $error);

        $this->assertTrue($result->isFailure());
        $this->assertSame($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    /**
     * map.
     */
    public function testMapOnSuccess(): void
    {
        $mapFn = fn (int $value) => $value + 10;
        $result = Result::succeed(11)->map($mapFn);
        $this->assertEquals(21, $result->unwrap(fn ($_) => 0));
    }

    public function testMapOnFailure(): void
    {
        $error = new \RuntimeException('error');
        $mapFn = $this->createClosureNotCalled();
        $result = Result::fail($error)->map($mapFn);

        $this->assertTrue($result->isFailure());
        $this->assertSame($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    /**
     * mapFailure.
     */
    public function testMapFailureOnSuccess(): void
    {
        /**
         * helping phpstan.
         *
         * @var callable(mixed):\Throwable
         */
        $mockClosureNotCalled = $this->createClosureNotCalled();

        $result = Result::succeed(11)->mapFailure($mockClosureNotCalled);
        $this->assertEquals(11, $result->unwrap($this->createClosureNotCalled()));
    }

    public function testMapFailureOnFailure(): void
    {
        $error1 = new \RuntimeException('error1');
        $error2 = new \RuntimeException('error2');

        $mockClosure = $this->createClosureMock();
        $mockClosure->expects($this->once())->method('__invoke')->with($error1)->willReturn($error2);

        /**
         * helping phpstan.
         *
         * @var callable(mixed):\Throwable
         */
        $mockClosureNotCalled = $this->createClosureNotCalled();

        /**
         * helping phpstan.
         *
         * @var callable(\Throwable):\Throwable $mockClosure
         */
        $result = Result::fail($error1)->mapFailure($mockClosure);
        $this->assertTrue($result->isFailure());
        $this->assertEquals($error2, $result->unwrapFailure($mockClosureNotCalled));
    }

    /**
     * mapBoth.
     */
    public function testMapBothOnSuccess(): void
    {
        $input = 10;
        $output = 20;

        $onSuccessMockClosure = $this->createClosureMock();
        $onSuccessMockClosure->expects($this->once())->method('__invoke')->with($input)->willReturn($output);

        /**
         * helping phpstan.
         *
         * @var callable(mixed):\Throwable
         */
        $mockOnFailureClosureNotCalled = $this->createClosureNotCalled();

        $result = Result::succeed($input)->mapBoth($onSuccessMockClosure, $mockOnFailureClosureNotCalled);
        $this->assertEquals($output, $result->unwrap($this->createClosureNotCalled()));
    }

    public function testMapBothOnFailure(): void
    {
        $inputError = new \RuntimeException('error1');
        $outputError = new \RuntimeException('error2');

        $onFailureMockClosure = $this->createClosureMock();
        $onFailureMockClosure->expects($this->once())->method('__invoke')->with($inputError)->willReturn($outputError);

        /**
         * helping phpstan.
         *
         * @var callable(\Throwable):\Throwable $onFailureMockClosure
         */
        $result = Result::fail($inputError)->mapBoth($this->createClosureNotCalled(), $onFailureMockClosure);
        $this->assertEquals($outputError, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    /**
     * flatmap.
     */
    public function testFlatmapOnSuccess(): void
    {
        $flatmapFn = fn (int $n) => Result::succeed($n + 10);
        $result = Result::succeed(11)->flatMap($flatmapFn);
        $this->assertEquals(21, $result->unwrap($this->createClosureNotCalled()));
    }

    public function testFlatmapOnFailure(): void
    {
        $error = new \RuntimeException('error');

        /**
         * helping phpstan.
         *
         * @var callable(mixed):Result<mixed,\Throwable> $flatmapFn
         */
        $flatmapFn = $this->createClosureNotCalled();
        $result = Result::fail($error)->flatMap($flatmapFn);
        $this->assertTrue($result->isFailure());
        $this->assertSame($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }

    public function testFlatmapReturnFailureOnSuccess(): void
    {
        $error = new \RuntimeException('error');

        $flatmapFn = fn (int $n) => Result::fail($error);
        $result = Result::succeed(11)->flatMap($flatmapFn);
        $this->assertTrue($result->isFailure());
        $this->assertSame($error, $result->unwrapFailure($this->createClosureNotCalled()));
    }
}
