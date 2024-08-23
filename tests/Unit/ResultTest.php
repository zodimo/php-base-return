<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Option;
use Zodimo\BaseReturn\Result;

/**
 * @internal
 *
 * @coversNothing
 */
class ResultTest extends TestCase
{
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
        $result = Result::fail(10);
        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * isSuccess.
     */
    public function testSucceedIsSuccess(): void
    {
        $result = Result::succeed(10);
        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
    }

    /**
     * isFailure.
     */
    public function testFailIsFailure(): void
    {
        $result = Result::fail('error');
        $this->assertTrue($result->isFailure());
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
        $this->assertEquals(10, $option->unwrap(fn () => 'none'));
    }

    public function testFailSuccessOptionNone(): void
    {
        $result = Result::fail('error');
        $option = $result->success();
        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isNone());
    }

    /**
     * failure.
     */
    public function testFailFailureOptionSome(): void
    {
        $result = Result::fail('fail');
        $option = $result->failure();
        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isSome());
        $this->assertEquals('fail', $option->unwrap(fn () => 'none'));
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
        $this->assertEquals(10, $result->unwrap(fn ($_) => 'error'));
    }

    public function testFailureUnwrapCallsOnFailureCallback(): void
    {
        $result = Result::fail('error');
        $this->assertEquals('error', $result->unwrap(fn ($e) => $e));
    }

    /**
     * match.
     */
    public function testMatchOnSuccess(): void
    {
        $result = Result::succeed(10)->match(
            fn ($value) => $value + 10,
            fn ($_) => 'failure'
        );
        $this->assertEquals(20, $result);
    }

    public function testMatchOnFailure(): void
    {
        $result = Result::fail(10)->match(
            fn ($_) => 'success',
            fn ($e) => $e + 10
        );
        $this->assertEquals(20, $result);
    }

    /**
     * fromOption constructor.
     */
    public function testFromOpionSomeIsSuccess(): void
    {
        $option = Option::some(10);
        $result = Result::fromOption($option, fn () => 'none');
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(10, $result->unwrap(fn ($_) => 'failure'));
    }

    public function testFromOptionNoneIsFailure(): void
    {
        $option = Option::none();
        $result = Result::fromOption($option, fn () => 'none');
        $this->assertTrue($result->isFailure());
        $this->assertEquals('none', $result->unwrap(fn ($error) => $error));
    }

    /**
     * map.
     */
    public function testMapOnSuccess(): void
    {
        $mapFn = fn (int $value) => $value + 10;
        $result = Result::succeed(11)->map($mapFn);
        $this->assertEquals(21, $result->unwrap(fn ($_) => 'error'));
    }

    public function testMapOnFailure(): void
    {
        $mapFn = fn (int $value) => $value + 10;
        $result = Result::fail('fail')->map($mapFn);
        $this->assertTrue($result->isFailure());
        // unwrap and return the error for the test
        $this->assertEquals('fail', $result->unwrap(fn ($error) => $error));
    }

    /**
     * flatmap.
     */
    public function testFlatmapOnSuccess(): void
    {
        $flatmapFn = fn (int $n) => Result::succeed($n + 10);
        $result = Result::succeed(11)->flatMap($flatmapFn);
        $this->assertEquals(21, $result->unwrap(fn ($_) => 'error'));
    }

    public function testFlatmapOnFailure(): void
    {
        $flatmapFn = fn (int $n) => Result::succeed($n + 10);
        $result = Result::fail('fail')->flatMap($flatmapFn);
        $this->assertEquals('fail', $result->unwrap(fn ($error) => $error));
    }

    public function testFlatmapReturnFailureOnSuccess(): void
    {
        $flatmapFn = fn (int $n) => Result::fail('fail');
        $result = Result::succeed(11)->flatMap($flatmapFn);
        $this->assertEquals('fail', $result->unwrap(fn ($error) => $error));
    }
}
