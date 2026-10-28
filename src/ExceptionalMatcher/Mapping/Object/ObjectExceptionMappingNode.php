<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Path\PropertyPath;

/** @internal */
final class ObjectExceptionMappingNode implements ExceptionMappingNode
{
    public function __construct(
        private readonly object $object,
        private readonly ?ExceptionMappingNode $embracingNode,
        /** @var iterable<ExceptionMatcher> */
        private readonly iterable $properties,
    ) {
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->properties as $property) {
            if ($property->match($reciprocal)) {
                return true;
            }
        }

        return false;
    }

    /** @codeCoverageIgnore - not used */
    public function getOwner(): ?ExceptionMappingNode
    {
        return $this->embracingNode;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->embracingNode?->getPropertyPath()
            ?? PropertyPath::empty();
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getRootObject(): object
    {
        return $this->embracingNode?->getRootObject()
            ?? $this->object;
    }

    public function getValue(): object
    {
        return $this->object;
    }
}
