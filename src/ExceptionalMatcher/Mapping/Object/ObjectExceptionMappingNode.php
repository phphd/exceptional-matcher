<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Path\PropertyPath;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatcher;

/** @internal */
final class ObjectExceptionMappingNode implements ExceptionMappingNode
{
    public function __construct(
        private readonly object $object,
        private readonly ?ExceptionMappingNode $owner,
        /** @var iterable<ExceptionMatcher> */
        private readonly iterable $propertyRules,
    ) {
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->propertyRules as $rule) {
            if ($rule->match($reciprocal)) {
                return true;
            }
        }

        return false;
    }

    public function getOwner(): ?ExceptionMappingNode
    {
        return $this->owner;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->owner?->getPropertyPath() ?? PropertyPath::empty();
    }

    public function getEnclosingObject(): object
    {
        return $this->object;
    }

    public function getRootObject(): object
    {
        return $this->owner?->getRootObject() ?? $this->object;
    }

    public function getValue(): object
    {
        return $this->object;
    }
}
