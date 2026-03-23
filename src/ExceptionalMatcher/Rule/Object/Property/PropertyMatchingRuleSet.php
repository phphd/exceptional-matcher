<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

/** @internal */
final class PropertyMatchingRuleSet implements MatchingRule
{
    public function __construct(
        private readonly MatchingRule $owner,
        private readonly string $name,
        private readonly mixed $value,
        private readonly MatchingRule $ruleSet,
    ) {
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        return $this->ruleSet->process($reciprocal);
    }

    public function getOwner(): MatchingRule
    {
        return $this->owner;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->owner->getPropertyPath()->with($this->name);
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
        return $this->value;
    }
}
