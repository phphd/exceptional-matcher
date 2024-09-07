<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\PropertyCapturableException;

final class NotHandleableMessageStub
{
    #[ExceptionalValidation\Capture(PropertyCapturableException::class, 'not captured')]
    private int $property;

    public function __construct(int $property)
    {
        $this->property = $property;
    }
}
