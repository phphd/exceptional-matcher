<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception;

use PhPhD\ExceptionalValidation\Model\Condition\Exception\ValueException;
use RuntimeException;

final class SomeValueException extends RuntimeException implements ValueException
{
    public function __construct(
        private readonly mixed $value,
    ) {
        parent::__construct();
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
