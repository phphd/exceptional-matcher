<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\Tests\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Catch_;

#[ExceptionalValidation]
final class ConditionalMessage
{
    #[Catch_(ConditionallyCaughtException::class, 'oops', when: [self::class, 'firstPropertyMatchesException'])]
    private int $firstProperty;

    #[Catch_(ConditionallyCaughtException::class, 'oops', when: [self::class, 'secondPropertyMatchesException'])]
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
