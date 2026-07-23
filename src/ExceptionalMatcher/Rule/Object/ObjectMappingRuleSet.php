<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\MappingRule;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

/** @internal */
final class ObjectMappingRuleSet implements MappingRule
{
    public function __construct(
        private readonly object $object,
        private readonly ?MappingRule $owner,
        /** @var iterable<ExceptionMatchingRule> */
        private readonly iterable $propertyRules,
    ) {
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->propertyRules as $rule) {
            if ($rule->process($reciprocal)) {
                return true;
            }
        }

        return false;
    }

    public function getOwner(): ?MappingRule
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
