<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zodimo\BaseReturn\Tuple;

/**
 * @internal
 *
 * @coversNothing
 */
class TupleTest extends TestCase
{
    public function testCanCreate(): void
    {
        $tuple = Tuple::create(10, 11);
        $this->assertInstanceOf(Tuple::class, $tuple);
    }

    public function testCanRetrieve(): void
    {
        $tuple = Tuple::create(10, 11);
        $this->assertEquals(10, $tuple->fst());
        $this->assertEquals(11, $tuple->snd());
    }

    public function testSwap(): void
    {
        $tuple = Tuple::create(10, 11)->swap();
        $this->assertEquals(10, $tuple->snd());
        $this->assertEquals(11, $tuple->fst());
    }
}
