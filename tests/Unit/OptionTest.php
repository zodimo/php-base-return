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
    public function testSome(): void
    {
        $option = Option::some(10);
        $this->assertInstanceOf(Option::class, $option);
    }

    public function testSomeUnwrap(): void
    {
        $option = Option::some(10);
        $this->assertEquals(10, $option->unwrap(fn () => 'none'));
    }

    public function testNone(): void
    {
        $option = Option::none();
        $this->assertInstanceOf(Option::class, $option);
    }

    public function testNoneUnwrap(): void
    {
        $option = Option::none();
        $this->assertInstanceOf(Option::class, $option);

        $this->assertEquals('none', $option->unwrap(fn () => 'none'));
    }
}
