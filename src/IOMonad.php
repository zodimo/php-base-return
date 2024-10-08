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
     * @var Result<VALUE,ERR>
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
     * Sequential computation on a successful value.
     *
     * @template _OUTPUTF
     * @template _ERRF
     *
     * @param callable(VALUE):IOMonad<_OUTPUTF,_ERRF> $f
     *
     * @return IOMonad<_OUTPUTF,_ERRF>|IOMonad<_OUTPUTF,ERR>
     */
    public function flatMap(callable $f): IOMonad
    {
        return $this->_result->match(
            fn ($value) => $f($value),
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
     * @return IOMonad<_OUTPUTF,ERR>
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
     * @return IOMonad<_VALUE,mixed>
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
     * @return IOMonad<mixed,_ERR>
     */
    public static function fail($e): IOMonad
    {
        return new self(Result::fail($e));
    }

    /**
     * @phpstan-assert-if-true IOMonad<VALUE, never> $this
     *
     * @phpstan-assert-if-false IOMonad<never, ERR> $this
     */
    public function isSuccess(): bool
    {
        return $this->_result->isSuccess();
    }

    /**
     * @phpstan-assert-if-true IOMonad<never, ERR> $this
     *
     * @phpstan-assert-if-false IOMonad<VALUE, never> $this
     */
    public function isFailure(): bool
    {
        return $this->_result->isFailure();
    }

    /**
     * @template WRAPPEDVALUE of VALUE
     *
     * @param callable(ERR):WRAPPEDVALUE $onFailure
     *
     * @return VALUE
     */
    public function unwrapSuccess(callable $onFailure)
    {
        return $this->_result->unwrap($onFailure);
    }

    /**
     * @template WRAPPEDERR of ERR
     *
     * @param callable(VALUE):WRAPPEDERR $onSuccess
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
     * @return IOMonad<_VALUE,\Throwable>
     */
    public static function try(callable $f): IOMonad
    {
        try {
            return IOMonad::pure($f());
        } catch (\Throwable $e) {
            return IOMonad::fail($e);
        }
    }

    // ///////////////////////
    // / CONVENIENCE METHODS
    // ///////////////////////

    /**
     * Like FlatMap but return ignores the result of the computation and propagate the failure.
     *
     * @template _ERRF
     *
     * @param callable(VALUE):IOMonad<mixed,_ERRF> $f
     *
     * @return IOMonad<VALUE,_ERRF>|IOMonad<VALUE,ERR>
     */
    public function tapSuccess(callable $f): IOMonad
    {
        return $this->_result->match(
            fn ($value) => $f($value)->flatMap(fn ($_) => $this),
            fn ($_) => $this
        );
    }

    /**
     * Like tapSuccess but for failures.
     *
     * @template _ERRF
     *
     * @param callable(ERR):IOMonad<mixed,_ERRF> $f
     *
     * @return IOMonad<VALUE,_ERRF>|IOMonad<VALUE,ERR>
     */
    public function tapFailure(callable $f): IOMonad
    {
        return $this->_result->match(
            fn ($_) => $this,
            fn ($failure) => $f($failure)->flatMap(fn ($_) => $this)
        );
    }

    /**
     * Like fmap, but on failure.
     *
     * @template _OUTPUTF
     *
     * @param callable(ERR):_OUTPUTF $f
     *
     * @return IOMonad<VALUE,_OUTPUTF>
     */
    public function fmapFailure(callable $f): IOMonad
    {
        return new IOMonad($this->_result->mapFailure($f));
    }

    /**
     * Like flatMap, but on failure.
     *
     * @template _OUTPUTF
     * @template _ERRF
     *
     * @param callable(ERR):IOMonad<_OUTPUTF,_ERRF> $f
     *
     * @return IOMonad<_OUTPUTF,_ERRF>|IOMonad<VALUE,ERR>
     */
    public function flatMapFailure(callable $f): IOMonad
    {
        return $this->_result->match(
            fn ($_) => $this,
            fn ($error) => $f($error),
        );
    }
}
