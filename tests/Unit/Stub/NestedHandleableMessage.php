<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Stub;

use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\NestedPropertyCapturableException;

#[ExceptionalValidation]
final class NestedHandleableMessage
{
    #[ExceptionalValidation\Capture(NestedPropertyCapturableException::class, 'nested.message')]
    private string $nestedProperty;
}
