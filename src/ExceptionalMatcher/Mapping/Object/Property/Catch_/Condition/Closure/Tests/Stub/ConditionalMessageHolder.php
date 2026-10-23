<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\Tests\Stub;

use PhPhD\ExceptionalMatcher\Mapping\Object\Try_;

#[Try_]
final class ConditionalMessageHolder
{
    public function __construct(
        public ConditionalMessage $conditionalMessage,
    ) {
    }
}
