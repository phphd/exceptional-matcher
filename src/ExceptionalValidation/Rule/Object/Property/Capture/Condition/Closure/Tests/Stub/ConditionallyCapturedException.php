<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\Tests\Stub;

use RuntimeException;

final class ConditionallyCapturedException extends RuntimeException
{
    public function __construct(
        private readonly int $conditionValue,
    ) {
        parent::__construct();
    }

    public function getConditionValue(): int
    {
        return $this->conditionValue;
    }
}
