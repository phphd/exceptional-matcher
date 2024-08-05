<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Stub\Exception;

use PhPhD\ExceptionalValidation\Model\Condition\Exception\InvalidValueException;
use RuntimeException;

final class SomeInvalidValueException extends RuntimeException implements InvalidValueException
{
    public function __construct(
        private readonly mixed $value,
    ) {
        parent::__construct();
    }

    public function getInvalidValue(): mixed
    {
        return $this->value;
    }
}
