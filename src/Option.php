<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn;

/**
 * @template T
 */
class Option
{
    private const someTag = 'some';
    private const noneTag = 'none';

    private string $_tag;

    /**
     * @var T
     */
    private $value;

    /**
     * @param T $value
     */
    private function __construct(string $_tag, $value)
    {
        $this->_tag = $_tag;
        $this->value = $value;
    }

    /**
     * @param T $value
     *
     * @return Option<T>
     */
    public static function some($value): Option
    {
        return new self(self::someTag, $value);
    }

    /**
     * @return Option<mixed>
     */
    public static function none(): Option
    {
        return new self(self::noneTag, null);
    }

    /**
     * @phpstan-assert-if-true Option<T> $this
     *
     * @phpstan-assert-if-false Option<void> $this
     */
    public function isSome(): bool
    {
        return self::someTag == $this->_tag;
    }

    /**
     * @phpstan-assert-if-true Option<void> $this
     *
     * @phpstan-assert-if-false Option<T> $this
     */
    public function isNone(): bool
    {
        return self::noneTag == $this->_tag;
    }

    /**
     * Unwrap Some value or call onNone to return a default/alternative value.
     *
     * @param callable():T $defaultOnNone
     *
     * @return T
     */
    public function unwrap(callable $defaultOnNone)
    {
        return $this->match(
            // identity
            fn ($x) => $x,
            $defaultOnNone
        );
    }

    /**
     * @template T2
     *
     * @param callable(T):T2 $onSome
     * @param callable():T2  $onNone
     *
     * @return T2
     */
    public function match(callable $onSome, callable $onNone)
    {
        if ($this->isSome()) {
            return call_user_func($onSome, $this->value);
        }

        return call_user_func($onNone);
    }

    /**
     * @template T2
     *
     * @param callable(T):T2 $fn
     *
     * @return Option<T2>
     */
    public function map(callable $fn): Option
    {
        if ($this->isSome()) {
            $clone = clone $this;
            $clone->value = call_user_func($fn, $this->value);

            return $clone;
        }

        return $this;
    }

    /**
     * @template T2
     *
     * @param callable(T):Option<T2> $fn
     *
     * @return Option<T2>
     */
    public function flatMap(callable $fn): Option
    {
        if ($this->isSome()) {
            return call_user_func($fn, $this->value);
        }

        return $this;
    }
}
