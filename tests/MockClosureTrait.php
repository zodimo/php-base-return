<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn\Tests;

use PHPUnit\Framework\MockObject\MockObject;

trait MockClosureTrait
{
    /**
     * @return callable&MockObject $mockClosure
     */
    public function createClosureMock()
    {
        return $this
            ->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock()
        ;
    }
}
