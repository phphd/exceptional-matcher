<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

#[Try_]
final class ConditionalMessage
{
    #[Catch_(ConditionallyCaughtException::class, if: [self::class, 'firstPropertyMatchesException'], message: 'oops')]
    private int $firstProperty;

    #[Catch_(ConditionallyCaughtException::class, if: [self::class, 'secondPropertyMatchesException'], message: 'oops')]
    private int $secondProperty;

    public static function createWithConditionalProperties(int $firstConditionalProperty, int $secondConditionalProperty): self
    {
        $message = new self();
        $message->firstProperty = $firstConditionalProperty;
        $message->secondProperty = $secondConditionalProperty;

        return $message;
    }

    /** @api */
    public function firstPropertyMatchesException(ConditionallyCaughtException $exception): bool
    {
        return $exception->getConditionValue() === $this->firstProperty;
    }

    /** @api */
    public function secondPropertyMatchesException(ConditionallyCaughtException $exception): bool
    {
        return $exception->getConditionValue() === $this->secondProperty;
    }
}
