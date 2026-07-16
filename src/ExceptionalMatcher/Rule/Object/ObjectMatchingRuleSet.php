<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

/** @internal */
final class ObjectMatchingRuleSet implements MatchingRule
{
    public function __construct(
        private readonly object $object,
        private readonly ?MatchingRule $owner,
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

    public function getOwner(): ?MatchingRule
    {
        return $this->owner;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->owner?->getPropertyPath() ?? PropertyPath::empty();
    }

    public function getEnclosingObject(): object
    {
        return $this->object;
    }

    public function getRootObject(): object
    {
        return $this->owner?->getRootObject() ?? $this->object;
    }

    public function getValue(): object
    {
        return $this->object;
    }
}
