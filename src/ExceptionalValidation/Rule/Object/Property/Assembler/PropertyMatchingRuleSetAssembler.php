<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\LazyMatchingRule;
use PhPhD\ExceptionalValidation\Rule\MatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\ObjectMatchingRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyMatchingRuleSet;
use ReflectionProperty;

/** @internal */
final class PropertyMatchingRuleSetAssembler implements MatchingRuleSetAssembler
{
    public function __construct(
        private readonly ObjectMatchingRuleSet $parentRule,
        private readonly ReflectionProperty $reflectionProperty,
    ) {
    }

    public function assemble(PropertyMatchingRuleSetAssemblerService $service): ?MatchingRule
    {
        $matchingRuleSet = new LazyMatchingRule(
            /** @param LazyMatchingRule<MatchingRule> $lazyMatchingRuleSet */
            function (LazyMatchingRule $lazyMatchingRuleSet) use ($service): ?MatchingRule {
                $object = $this->parentRule->getValue();

                $propertyRuleSet = new PropertyMatchingRuleSet(
                    $this->parentRule,
                    $this->getName(),
                    $this->getPropertyValue($object),
                    $lazyMatchingRuleSet,
                );

                return $service->matchingRulesAssemblerService
                    ->assemble(new PropertyMatchingRulesAssembler($propertyRuleSet, $this->reflectionProperty))
                ;
            },
        );

        return $matchingRuleSet->build()?->getParent();
    }

    public function getParentRule(): ObjectMatchingRuleSet
    {
        return $this->parentRule;
    }

    private function getName(): string
    {
        return $this->reflectionProperty->getName();
    }

    private function getPropertyValue(object $message): mixed
    {
        if (!$this->reflectionProperty->isInitialized($message)) {
            return null;
        }

        return $this->reflectionProperty->getValue($message);
    }
}
