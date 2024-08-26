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
     * @param T $value
     *
     * @return Result<T, mixed>
     */
    public static function succeed($value): Result
    {
        return new Result(self::successTag, $value);
    }

    /**
     * Fail with this $value as failure.
     *
     * @param E $value
     *
     * @return Result<mixed, E>
     */
    public static function fail($value): Result
    {
        return new Result(self::failureTag, $value);
    }

    /**
     * @phpstan-assert-if-true Result<T, mixed> $this
     */
    public function isSuccess(): bool
    {
        return self::successTag === $this->_tag;
    }

    /**
     * @phpstan-assert-if-true Result<mixed, E> $this
     */
    public function isFailure(): bool
    {
        return self::failureTag === $this->_tag;
    }

    /**
     * Unwrap success value or call onFailure to return a default/alternative value.
     *
     * @param callable(E):T $defaultOnFailure
     *
     * @return T
     */
    public function unwrap(callable $defaultOnFailure)
    {
        if ($this->isFailure()) {
            return call_user_func($defaultOnFailure, $this->value);
        }

        return $this->value;
    }

    /**
     * Unwrap failure value or call onSuccess to return a default/alternative value.
     *
     * @param callable(T):E $defaultOnSuccess
     *
     * @return E
     */
    public function unwrapFailure(callable $defaultOnSuccess)
    {
        if ($this->isSuccess()) {
            return call_user_func($defaultOnSuccess, $this->value);
        }

        return $this->value;
    }

    /**
     * @return Option<T>
     */
    public function success(): Option
    {
        if ($this->isSuccess()) {
            return Option::some($this->value);
        }

        return Option::none();
    }

    /**
     * @return Option<E>
     */
    public function failure(): Option
    {
        if ($this->isFailure()) {
            return Option::some($this->value);
        }

        return Option::none();
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
            return call_user_func($onSuccess, $this->value);
        }

        return call_user_func($onFailure, $this->value);
    }

    /**
     * @param Option<T>    $option
     * @param callable():E $onNone
     *
     * @return Result<T,E>
     */
    public static function fromOption(Option $option, callable $onNone): Result
    {
        return $option->match(
            fn ($some) => Result::succeed($some),
            fn () => Result::fail(call_user_func($onNone)),
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
            $clone->value = call_user_func($fn, $this->value);

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
            $clone->value = call_user_func($fn, $this->value);

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
            $clone->value = call_user_func($onSuccess, $this->value);
        } else {
            $clone->value = call_user_func($onFailure, $this->value);
        }

        return $clone;
    }

    /**
     * @template T2
     * @template E2
     *
     * @param callable(T):Result<T2, E|E2> $fn
     *
     * @return Result<T2, E|E2>
     */
    public function flatMap(callable $fn): Result
    {
        if ($this->isSuccess()) {
            return call_user_func($fn, $this->value);
        }

        return $this;
    }
}
