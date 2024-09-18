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
     * @var Result<VALUE, ERR>
     */
    private Result $_result;

    /**
     * @param Result<VALUE,ERR> $result
     */
    private function __construct(Result $result)
    {
        $this->_result = $result;
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
        return $this->_result->match(
            fn ($value) => call_user_func($f, $value),
            fn ($_) => $this
        );
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
        return new IOMonad($this->_result->map($f));
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
        return new self(Result::succeed($a));
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
        return new self(Result::fail($e));
    }

    /**
     * give access to the Result.
     */
    public function isSuccess(): bool
    {
        return $this->_result->isSuccess();
    }

    public function isFailure(): bool
    {
        return $this->_result->isFailure();
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
        return $this->_result->unwrap($onFailure);
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
        return $this->_result->unwrapFailure($onSuccess);
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
        return $this->_result->match(
            $onSuccess,
            $onFailure
        );
    }

    /**
     * @template _VALUE
     *
     * @param callable():_VALUE $f
     *
     * @return IOMonad<_VALUE, \Throwable>
     */
    public static function try(callable $f): IOMonad
    {
        try {
            return IOMonad::pure(call_user_func($f));
        } catch (\Throwable $e) {
            return IOMonad::fail($e);
        }
    }
}
