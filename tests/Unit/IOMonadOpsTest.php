<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\IOMonad;
use Zodimo\BaseReturn\IOMonadOps;
use Zodimo\BaseReturnTest\MockClosureTrait;

/**
 * @internal
 *
 * @coversNothing
 */
class IOMonadOpsTest extends TestCase
{
    use MockClosureTrait;

    public function testSequenceEmptyList(): void
    {
        // @phpstan-ignore argument.templateType, argument.templateType
        $this->assertEquals([], IOMonadOps::sequence([])->unwrapSuccess($this->createClosureNotCalled()));
    }

    public function testSequenceValidList(): void
    {
        $input = [
            IOMonad::pure(1),
            IOMonad::pure(2),
            IOMonad::pure(3),
        ];

        $this->assertEquals([1, 2, 3], IOMonadOps::sequence($input)->unwrapSuccess($this->createClosureNotCalled()));
    }

    public function testSequenceReturnFirstErrorInList(): void
    {
        $error = new \RuntimeException('error');
        $input = [
            IOMonad::pure(1),
            IOMonad::fail($error),
            IOMonad::pure(new \InvalidArgumentException('error')),
        ];

        // @phpstan-ignore argument.type
        $this->assertSame($error, IOMonadOps::sequence($input)->unwrapFailure($this->createClosureNotCalled()));
    }
}
