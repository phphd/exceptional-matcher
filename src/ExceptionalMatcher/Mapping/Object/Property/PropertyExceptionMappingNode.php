<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Path\PropertyPath;

/** @internal */
final class PropertyExceptionMappingNode implements ExceptionMappingNode
{
    public function __construct(
        private readonly ExceptionMappingNode $object,
        private readonly string $name,
        private readonly mixed $value,
        /** @var iterable<ExceptionMatcher> $matchers */
        private readonly iterable $matchers,
    ) {
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->matchers as $matcher) {
            if ($matcher->match($reciprocal)) {
                return true;
            }
        }

        return false;
    }

    public function getOwner(): ExceptionMappingNode
    {
        return $this->object;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->object->getPropertyPath()
            ->with($this->name)
        ;
    }

    public function getEnclosingObject(): object
    {
        return $this->object->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->object->getRootObject();
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
