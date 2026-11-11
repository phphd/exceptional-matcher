<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Closure\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Try_;

#[Try_]
final class ConditionalMessageHolder
{
    public function __construct(
        public ConditionalMessage $conditionalMessage,
    ) {
    }
}
