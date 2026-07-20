<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

abstract class MatchingRuleDecorator implements MatchingRule
{
    public function __construct(
        protected MatchingRule $ownerRule,
    ) {
    }

    abstract public function process(ExceptionReciprocal $reciprocal): bool;

    final public function getOwner(): MatchingRule
    {
        return $this->ownerRule;
    }

    final public function getPropertyPath(): PropertyPath
    {
        return $this->ownerRule->getPropertyPath();
    }

    final public function getEnclosingObject(): object
    {
        return $this->ownerRule->getEnclosingObject();
    }

    final public function getRootObject(): object
    {
        return $this->ownerRule->getRootObject();
    }

    final public function getValue(): mixed
    {
        return $this->ownerRule->getValue();
    }
}
