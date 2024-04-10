<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\ConditionallyCapturedException;

#[ExceptionalValidation]
final class ConditionalMessage
{
    #[ExceptionalValidation\Capture(ConditionallyCapturedException::class, 'oops', when: [self::class, 'firstPropertyMatchesException'])]
    private int $firstProperty;

    #[ExceptionalValidation\Capture(ConditionallyCapturedException::class, 'oops', when: [self::class, 'secondPropertyMatchesException'])]
    private int $secondProperty;

    public static function createWithConditionalProperties(int $firstConditionalProperty, int $secondConditionalProperty): self
    {
        $message = new self();
        $message->firstProperty = $firstConditionalProperty;
        $message->secondProperty = $secondConditionalProperty;

        return $message;
    }

    public function firstPropertyMatchesException(ConditionallyCapturedException $exception): bool
    {
        return $exception->getConditionValue() === $this->firstProperty;
    }

    public function secondPropertyMatchesException(ConditionallyCapturedException $exception): bool
    {
        return $exception->getConditionValue() === $this->secondProperty;
    }
}
