<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\MappingRule;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

/** @internal */
final class PropertyMappingRuleSet implements MappingRule
{
    public function __construct(
        private readonly MappingRule $objectRule,
        private readonly string $name,
        private readonly mixed $value,
        /** @var iterable<ExceptionMatchingRule> $matchingRules */
        private readonly iterable $matchingRules,
    ) {
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        foreach ($this->matchingRules as $rule) {
            if ($rule->process($reciprocal)) {
                return true;
            }
        }

        return false;
    }

    public function getOwner(): MappingRule
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
