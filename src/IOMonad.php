<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn;

/**
 * A represent the value of a successful computation
 * E represent the value of a failed computation.
 *
 * @template VALUE
 * @template ERR
 */
class IOMonad
{
    /**
     * @var callable():Result<VALUE, ERR>
     */
    private $_thunk;

    /**
     * @var ?Result<VALUE, ERR>
     */
    private ?Result $evalResult;

    private function __construct(callable $thunk)
    {
        $this->_thunk = $thunk;
        $this->evalResult = null;
    }

    /**
     * Monadic bind >>=.
     *
     * @template _OUTPUTF
     * @template _ERRF
     *
     * @param callable(VALUE):IOMonad<_OUTPUTF, _ERRF> $f
     *
     * @return IOMonad<_OUTPUTF, _ERRF>|IOMonad<VALUE, ERR>
     */
    public function flatMap(callable $f): IOMonad
    {
        return new IOMonad(fn () => $this->eval()->match(
            fn ($value) => call_user_func($f, $value)->eval(),
            fn ($_) => $this->eval()
        ));
    }

    /**
     * Functor fmap.
     *
     * @template _OUTPUTF
     *
     * @param callable(VALUE):_OUTPUTF $f
     *
     * @return IOMonad<_OUTPUTF, ERR>
     */
    public function fmap(callable $f): IOMonad
    {
        return new IOMonad(fn () => $this->eval()->map($f));
    }

    /**
     * Monadic return or applicative pure.
     *
     * @template _VALUE
     *
     * @param _VALUE $a
     *
     * @return IOMonad<_VALUE, mixed>
     */
    public static function pure($a): IOMonad
    {
        return new self(fn () => Result::succeed($a));
    }

    /**
     * @template _ERR
     *
     * @param _ERR $e
     *
     * @return IOMonad<mixed, _ERR>
     */
    public static function fail($e): IOMonad
    {
        return new self(fn () => Result::fail($e));
    }

    /**
     * give access to the Result.
     */
    public function isSuccess(): bool
    {
        return $this->eval()->isSuccess();
    }

    public function isFailure(): bool
    {
        return $this->eval()->isFailure();
    }

    /**
     * For testing.
     *
     * @param callable(ERR):VALUE $onFailure
     *
     * @return VALUE
     */
    public function unwrapSuccess(callable $onFailure)
    {
        return $this->eval()->unwrap($onFailure);
    }

    /**
     *  For testing.
     *
     * @param callable(VALUE):ERR $onSuccess
     *
     * @return ERR
     */
    public function unwrapFailure(callable $onSuccess)
    {
        return $this->eval()->unwrapFailure($onSuccess);
    }

    /**
     * @template OUTPUT
     *
     * @param callable(VALUE):OUTPUT $onSuccess
     * @param callable(ERR):OUTPUT   $onFailure
     *
     * @return OUTPUT
     */
    public function match(callable $onSuccess, callable $onFailure)
    {
        return $this->eval()->match(
            $onSuccess,
            $onFailure
        );
    }

    /**
     * @return Result<VALUE, ERR>
     */
    private function eval(): Result
    {
        if (is_null($this->evalResult)) {
            $this->evalResult = call_user_func($this->_thunk);
        }

        return $this->evalResult;
    }
}
