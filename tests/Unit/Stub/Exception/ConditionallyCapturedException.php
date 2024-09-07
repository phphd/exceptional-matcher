<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception;

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
