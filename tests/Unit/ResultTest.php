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
    public function testSucceed()
    {
        $result = Result::succeed(10);
        $this->assertInstanceOf(Result::class, $result);
    }

    public function testOptionSuccess()
    {
        $result = Result::succeed(10);
        $option = $result->success();
        $this->assertInstanceOf(Option::class, $option);
        $this->assertEquals(10, $option->unwrap(fn () => 'none'));
    }

    public function testSucceedUnwrap()
    {
        $result = Result::succeed(10);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(10, $result->unwrap(fn ($_) => 'error'));
    }

    public function testFail()
    {
        $result = Result::fail(10);
        $this->assertInstanceOf(Result::class, $result);
        $option = $result->failure();
        $this->assertInstanceOf(Option::class, $option);
        $this->assertEquals(10, $option->unwrap(fn () => 'none'));
    }

    public function testOptionFailure()
    {
        $result = Result::fail(10);
        $this->assertInstanceOf(Result::class, $result);
        $option = $result->failure();
        $this->assertInstanceOf(Option::class, $option);
        $this->assertEquals(10, $option->unwrap(fn () => 'none'));
    }

    public function testFailureUnwrap()
    {
        $result = Result::fail(10);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(10, $result->unwrap(fn ($e) => $e));
    }
}
