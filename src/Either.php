<?php

declare(strict_types=1);

namespace Zodimo\BaseReturn;

/**
 * @template LEFT
 * @template RIGHT
 */
class Either
{
    private const leftTag = 'left';
    private const rightTag = 'right';

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
     * Constructor for left.
     *
     * @template L
     *
     * @param L $value
     *
     * @return Either<L, mixed>
     */
    public static function left($value): Either
    {
        return new Either(self::leftTag, $value);
    }

    /**
     * Constructor for Right.
     *
     * @template R
     *
     * @param R $value
     *
     * @return Either<mixed, R>
     */
    public static function right($value): Either
    {
        return new Either(self::rightTag, $value);
    }

    /**
     * @phpstan-assert-if-true Either<LEFT, mixed> $this
     *
     * @phpstan-assert-if-false Either<mixed, RIGHT> $this
     */
    public function isLeft(): bool
    {
        return self::leftTag === $this->_tag;
    }

    /**
     * @phpstan-assert-if-true Either<mixed, RIGHT> $this
     *
     * @phpstan-assert-if-false Either<LEFT, mixed> $this
     */
    public function isRight(): bool
    {
        return self::rightTag === $this->_tag;
    }

    /**
     * Unwrap Left value or call defaultOnRight to return a default/alternative value.
     *
     * @param callable(RIGHT):LEFT $defaultOnRight
     *
     * @return LEFT
     */
    public function unwrapLeft(callable $defaultOnRight)
    {
        return $this->match(
            // identity
            fn ($x) => $x,
            $defaultOnRight
        );
    }

    /**
     * Unwrap Right value or call defaultOnLeft to return a default/alternative value.
     *
     * @param callable(LEFT):RIGHT $defaultOnLeft
     *
     * @return RIGHT
     */
    public function unwrapRight(callable $defaultOnLeft)
    {
        return $this->match(
            $defaultOnLeft,
            // identity
            fn ($x) => $x,
        );
    }

    /**
     * @template RETURN
     *
     * @param callable(LEFT):RETURN  $onLeft
     * @param callable(RIGHT):RETURN $onRight
     *
     * @return RETURN
     */
    public function match(callable $onLeft, callable $onRight)
    {
        if ($this->isLeft()) {
            return call_user_func($onLeft, $this->value);
        }

        return call_user_func($onRight, $this->value);
    }

    /**
     * @template L
     *
     * @param callable(LEFT):L $fn
     *
     * @return Either<L,RIGHT>
     */
    public function mapLeft(callable $fn): Either
    {
        if ($this->isLeft()) {
            $clone = clone $this;
            $clone->value = call_user_func($fn, $this->value);

            return $clone;
        }

        return $this;
    }

    /**
     * @template R
     *
     * @param callable(RIGHT):R $fn
     *
     * @return Either<LEFT, R>
     */
    public function mapRight(callable $fn): Either
    {
        if ($this->isRight()) {
            $clone = clone $this;
            $clone->value = call_user_func($fn, $this->value);

            return $clone;
        }

        return $this;
    }

    /**
     * @template L
     * @template R
     *
     * @param callable(LEFT):L  $onLeft
     * @param callable(RIGHT):R $onRight
     *
     * @return Either<L, R>
     */
    public function mapBoth(callable $onLeft, callable $onRight): Either
    {
        $clone = clone $this;
        if ($this->isLeft()) {
            $clone->value = call_user_func($onLeft, $this->value);
        } else {
            $clone->value = call_user_func($onRight, $this->value);
        }

        return $clone;
    }

    /**
     * FlatMap on Right.
     *
     * @template R
     * @template L
     *
     * @param callable(RIGHT):Either<L|LEFT, R> $fn
     *
     * @return Either<L|LEFT, R>
     */
    public function flatMap(callable $fn): Either
    {
        if ($this->isRight()) {
            return call_user_func($fn, $this->value);
        }

        return $this;
    }
}
