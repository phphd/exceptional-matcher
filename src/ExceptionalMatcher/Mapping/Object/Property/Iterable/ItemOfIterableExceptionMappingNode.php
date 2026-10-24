<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Iterable;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Path\PropertyPath;

/**
 * @internal
 *
 * @template T of object
 */
final class ItemOfIterableExceptionMappingNode implements ExceptionMappingNode
{
    private readonly ExceptionMappingNode $objectNode;

    /**
     * @param T $value
     * @param ObjectExceptionMappingPlan<T> $matchingPlan
     */
    public function __construct(
        private readonly ExceptionMappingNode $propertyNode,
        private readonly int|string $key,
        private readonly object $value,
        ObjectExceptionMappingPlan $matchingPlan,
    ) {
        $this->objectNode = $matchingPlan->bind($this->value, $this);
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
            ->at($this->key);
    }

    public function getObject(): object
    {
        return $this->propertyNode->getObject();
    }

    public function getRootObject(): object
    {
        return $this->propertyNode->getRootObject();
    }

    /** @return T */
    public function getValue(): object
    {
        return $this->value;
    }
}
