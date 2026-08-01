<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule;

use LogicException;
use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Plan\ClassMappingPlan;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

use function is_object;

/** @internal */
final class ItemOfIterableExceptionMappingNode implements ExceptionMappingNode
{
    private readonly ExceptionMappingNode $ruleSet;

    public function __construct(
        private readonly ExceptionMappingNode $owner,
        private readonly int|string $key,
        private readonly mixed $item,
        ClassMappingPlan $matchingPlan,
    ) {
        $this->ruleSet = $matchingPlan->bind($this->item, $this);
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        return $this->ruleSet->match($reciprocal);
    }

    public function getOwner(): ExceptionMappingNode
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
