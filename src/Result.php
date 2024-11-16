<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn;

/**
 * @template T
 * @template E
 */
class Result
{
    private const successTag = 'success';
    private const failureTag = 'failure';

    private string $_tag;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    private function __construct(string $_tag, $value)
    {
        $this->_tag = $_tag;
        $this->value = $value;
    }

    /**
     * Succeed with this $value as success.
     *
     * @template _T
     *
     * @param _T $value
     *
     * @return Result<_T,mixed>
     */
    public static function succeed($value): Result
    {
        return new Result(self::successTag, $value);
    }

    /**
     * Fail with this $value as failure.
     *
     * @template _E
     *
     * @param _E $value
     *
     * @return Result<mixed,_E>
     */
    public static function fail($value): Result
    {
        return new Result(self::failureTag, $value);
    }

    /**
     * @phpstan-assert-if-true Result<T,never> $this
     *
     * @phpstan-assert-if-false Result<never,E> $this
     */
    public function isSuccess(): bool
    {
        return self::successTag === $this->_tag;
    }

    /**
     * @phpstan-assert-if-true Result<never,E> $this
     *
     * @phpstan-assert-if-false Result<T,never> $this
     */
    public function isFailure(): bool
    {
        return self::failureTag === $this->_tag;
    }

    /**
     * Unwrap success value or call onFailure to return a default/alternative value.
     *
     * @template DefaultT of T
     *
     * @param callable(E):DefaultT $defaultOnFailure
     *
     * @return T
     */
    public function unwrap(callable $defaultOnFailure)
    {
        return $this->match(
            // identity
            fn ($x) => $x,
            $defaultOnFailure
        );
    }

    /**
     * Unwrap failure value or call onSuccess to return a default/alternative value.
     *
     * @template WRAPPEDERR of E
     *
     * @param callable(T):WRAPPEDERR $defaultOnSuccess
     *
     * @return E
     */
    public function unwrapFailure(callable $defaultOnSuccess)
    {
        return $this->match(
            $defaultOnSuccess,
            // identity
            fn ($x) => $x,
        );
    }

    /**
     * @return Option<T>
     */
    public function success(): Option
    {
        return $this->match(
            fn ($value) => Option::some($value),
            fn () => Option::none()
        );
    }

    /**
     * @return Option<E>
     */
    public function failure(): Option
    {
        return $this->match(
            fn () => Option::none(),
            fn ($value) => Option::some($value),
        );
    }

    /**
     * @template T2
     *
     * @param callable(T):T2 $onSuccess
     * @param callable(E):T2 $onFailure
     *
     * @return T2
     */
    public function match(callable $onSuccess, callable $onFailure)
    {
        if ($this->isSuccess()) {
            return $onSuccess($this->value);
        }

        return $onFailure($this->value);
    }

    /**
     * Option::none() represent a failure type.
     *
     * @template _T
     * @template _E
     *
     * @param Option<_T>    $option
     * @param callable():_E $onNone
     *
     * @return Result<_T,_E>
     */
    public static function fromOption(Option $option, callable $onNone): Result
    {
        return $option->match(
            fn ($some) => Result::succeed($some),
            fn () => Result::fail($onNone()),
        );
    }

    /**
     * @template T2
     *
     * @param callable(T):T2 $fn
     *
     * @return Result<T2,E>
     */
    public function map(callable $fn): Result
    {
        if ($this->isSuccess()) {
            $clone = clone $this;
            $clone->value = $fn($this->value);

            return $clone;
        }

        return $this;
    }

    /**
     * @template E2
     *
     * @param callable(E):E2 $fn
     *
     * @return Result<T, E2>
     */
    public function mapFailure(callable $fn): Result
    {
        if ($this->isFailure()) {
            $clone = clone $this;
            $clone->value = $fn($this->value);

            return $clone;
        }

        return $this;
    }

    /**
     * @template T2
     * @template E2
     *
     * @param callable(T):T2 $onSuccess
     * @param callable(E):E2 $onFailure
     *
     * @return Result<T2, E2>
     */
    public function mapBoth(callable $onSuccess, callable $onFailure): Result
    {
        $clone = clone $this;
        if ($this->isSuccess()) {
            $clone->value = $onSuccess($this->value);
        } else {
            $clone->value = $onFailure($this->value);
        }

        return $clone;
    }

    /**
     * @template T2
     * @template E2
     *
     * @param callable(T):Result<T2, E2> $fn
     *
     * @return Result<T2,E2>|Result<T2,E>
     */
    public function flatMap(callable $fn): Result
    {
        if ($this->isSuccess()) {
            return $fn($this->value);
        }

        return $this;
    }
}
