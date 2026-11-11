<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Iterable;

use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\ExceptionReciprocal;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Path\PropertyPath;

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
     * @param ObjectExceptionMappingBlueprint<T> $print
     */
    public function __construct(
        private readonly ExceptionMappingNode $propertyNode,
        private readonly int|string $key,
        private readonly object $value,
        ObjectExceptionMappingBlueprint $print,
    ) {
        $this->objectNode = $print->bind($this, $this->value);
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        return $this->objectNode->match($reciprocal);
    }

    /** @codeCoverageIgnore - not used */
    public function getOwner(): ExceptionMappingNode
    {
        return $this->propertyNode;
    }

    public function getRootObject(): object
    {
        return $this->propertyNode->getRootObject();
    }

    public function getLeafObject(): object
    {
        return $this->propertyNode->getLeafObject();
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->propertyNode->getPropertyPath()
            ->at($this->key)
        ;
    }

    /** @return T */
    public function getValue(): object
    {
        return $this->value;
    }
}
