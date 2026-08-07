<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Unit\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\NestedPropertyMatchedException;

#[Try_]
final class NestedHandleableMessage
{
    #[Catch_(NestedPropertyMatchedException::class, message: 'nested.message')]
    private string $nestedProperty;
}
