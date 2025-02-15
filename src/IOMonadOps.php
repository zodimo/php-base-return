<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn;

class IOMonadOps
{
    /**
     * @template VALUE
     * @template ERR
     *
     * @param array<IOMonad<VALUE,ERR>> $values
     *
     * @return IOMonad<array<VALUE>, ERR>
     */
    public static function sequence(array $values): IOMonad
    {
        // sequence :: Monad m => Either a (m a0) -> m (Either a a0)
        // list of values or first error
        $output = [];
        foreach ($values as $value) {
            /**
             * @var IOMonad<VALUE,ERR> $value
             */
            if ($value->isFailure()) {
                return $value;
            }
            // @phpstan-ignore method.void
            $output[] = $value->unwrapSuccess(function () {});
        }

        // @phpstan-ignore return.type
        return IOMonad::pure($output);
    }
}
