<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use PhPhD\ExceptionalMatcher\Rule\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

/** @internal */
final class ObjectMatchingRuleSet implements MatchingRule
{
    public function __construct(
        private readonly object $object,
        private readonly ?MatchingRule $parent,
        private readonly MatchingRule $ruleSet,
    ) {
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        return $this->ruleSet->process($reciprocal);
    }

    public function getParent(): ?MatchingRule
    {
        return $this->parent;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent?->getPropertyPath() ?? PropertyPath::empty();
    }

    public function getEnclosingObject(): object
    {
        return $this->object;
    }

    public function getRootObject(): object
    {
        return $this->parent?->getRootObject() ?? $this->object;
    }

    public function getValue(): object
    {
        return $this->object;
    }
}
