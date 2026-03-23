<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

/** @internal */
final class CompositeMatchingRule implements MatchingRule
{
    public function __construct(
        private readonly MatchingRule $owner,
        /** @var iterable<MatchingRule> $rules */
        private readonly iterable $rules,
    ) {
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule->process($reciprocal)) {
                return true;
            }
        }

        return false;
    }

    public function getOwner(): MatchingRule
    {
        return $this->owner;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->owner->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return $this->owner->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->owner->getRootObject();
    }

    public function getValue(): mixed
    {
        return $this->owner->getValue();
    }
}
