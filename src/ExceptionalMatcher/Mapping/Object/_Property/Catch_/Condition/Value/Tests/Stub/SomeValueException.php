<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Value\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Value\ValueException;
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
