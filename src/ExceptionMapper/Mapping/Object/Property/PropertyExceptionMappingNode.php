<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property;

use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Matcher\ExceptionMatcher;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Path\PropertyPath;

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

    /** @codeCoverageIgnore - not used */
    public function getOwner(): ExceptionMappingNode
    {
        return $this->object;
    }

    public function getRootObject(): object
    {
        return $this->object->getRootObject();
    }

    public function getLeafObject(): object
    {
        return $this->object->getLeafObject();
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->object->getPropertyPath()
            ->with($this->name)
        ;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
