<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Closure\Tests\Stub;

use RuntimeException;

final class ConditionallyCaughtException extends RuntimeException
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
