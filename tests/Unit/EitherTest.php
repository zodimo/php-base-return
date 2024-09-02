<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Either;

/**
 * @internal
 *
 * @coversNothing
 */
class EitherTest extends TestCase
{
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
        $this->assertFalse($either->isRight());
    }

    public function testRightIsRight(): void
    {
        $either = Either::right(10);
        $this->assertTrue($either->isRight());
        $this->assertFalse($either->isLeft());
    }
}
