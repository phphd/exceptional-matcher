<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Path\PropertyPath;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRule;

/** @internal */
final class PropertyExceptionMappingNode implements ExceptionMappingNode
{
    public function __construct(
        private readonly ExceptionMappingNode $objectRule,
        private readonly string $name,
        private readonly mixed $value,
        /** @var iterable<ExceptionMatchingRule> $matchingRules */
        private readonly iterable $matchingRules,
    ) {
    }

    public function match(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->matchingRules as $rule) {
            if ($rule->match($reciprocal)) {
                return true;
            }
        }

        return false;
    }

    public function getOwner(): ExceptionMappingNode
    {
        return $this->objectRule;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->objectRule->getPropertyPath()
            ->with($this->name)
        ;
    }

    public function getEnclosingObject(): object
    {
        return $this->objectRule->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->objectRule->getRootObject();
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
