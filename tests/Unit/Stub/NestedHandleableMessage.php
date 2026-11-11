<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Tests\Unit\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionMapper\Mapping\Object\Try_;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\NestedPropertyMatchedException;

#[Try_]
final class NestedHandleableMessage
{
    #[Catch_(NestedPropertyMatchedException::class, message: 'nested.message')]
    private string $nestedProperty;
}
