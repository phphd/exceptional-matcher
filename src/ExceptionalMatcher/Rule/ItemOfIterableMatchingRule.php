<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule;

use LogicException;
use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Rule\Object\Plan\ClassMappingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

use function is_object;

/** @internal */
final class ItemOfIterableMatchingRule implements MatchingRule
{
    private readonly MatchingRule $ruleSet;

    public function __construct(
        private readonly MatchingRule $owner,
        private readonly int|string $key,
        private readonly mixed $item,
        ClassMappingPlan $matchingPlan,
    ) {
        $this->ruleSet = $matchingPlan->bind($this->item, $this);
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
        return $this->owner->getPropertyPath()
            ->at($this->key);
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
        return $this->item;
    }
}
