<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Iterable;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\ento\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Path\PropertyPath;

/** @internal */
final class ItemOfIterableExceptionMappingNode implements ExceptionMappingNode
{
    private readonly ExceptionMappingNode $ruleSet;

    public function __construct(
        private readonly ExceptionMappingNode $owner,
        private readonly int|string $key,
        private readonly mixed $item,
        ObjectExceptionMappingPlan $matchingPlan,
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
            ->at($this->key)
        ;
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
