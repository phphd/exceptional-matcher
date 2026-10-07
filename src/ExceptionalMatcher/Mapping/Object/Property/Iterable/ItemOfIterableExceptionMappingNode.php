<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Iterable;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Path\PropertyPath;

/** @internal */
final class ItemOfIterableExceptionMappingNode implements ExceptionMappingNode
{
    private readonly ExceptionMappingNode $objectNode;

    /** @param ObjectExceptionMappingPlan<object> $matchingPlan */
    public function __construct(
        private readonly ExceptionMappingNode $propertyNode,
        private readonly int|string $key,
        private readonly object $item,
        ObjectExceptionMappingPlan $matchingPlan,
    ) {
        $this->objectNode = $matchingPlan->bind($this->item, $this);
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        return $this->objectNode->match($reciprocal);
    }

    public function getOwner(): ExceptionMappingNode
    {
        return $this->propertyNode;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->propertyNode->getPropertyPath()
            ->at($this->key)
        ;
    }

    public function getEnclosingObject(): object
    {
        return $this->propertyNode->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->propertyNode->getRootObject();
    }

    public function getValue(): mixed
    {
        return $this->item;
    }
}
