<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Matcher;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\MappingRule;

final class CatchAttributesExceptionMatcher
{
    public function __construct(
        /** @var iterable<MappingRule> $rules */
        private readonly iterable $rules,
    ) {
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule->process($reciprocal)) {
                return true;
            }
        }

        return false;
    }
}
