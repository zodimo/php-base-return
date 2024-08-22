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
     * @param T $value
     *
     * @return Result<T, never>
     */
    public static function succeed($value): Result
    {
        return new Result(self::successTag, $value);
    }

    /**
     * @param E $value
     *
     * @return Result<never, E>
     */
    public static function fail($value): Result
    {
        return new Result(self::failureTag, $value);
    }

    public function isSuccess(): bool
    {
        return self::successTag === $this->_tag;
    }

    public function isFailure(): bool
    {
        return self::failureTag === $this->_tag;
    }

    /**
     * @param callable(E):mixed $onFailure
     *
     * @return mixed|T
     */
    public function unwrap(callable $onFailure)
    {
        if ($this->isFailure()) {
            return call_user_func($onFailure, $this->value);
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
    public function fromOption(Option $option, callable $onNone): Result
    {
        return $option->match(
            fn ($some) => $this->succeed($some),
            fn () => $this->fail(call_user_func($onNone)),
        );
    }

    /**
     * @param callable(T):T $fn
     *
     * @return Result<T,E>
     */
    public function map(callable $fn): Result
    {
        if ($this->isSuccess()) {
            return $this->succeed(call_user_func($fn, $this->value));
        }

        return $this;
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
