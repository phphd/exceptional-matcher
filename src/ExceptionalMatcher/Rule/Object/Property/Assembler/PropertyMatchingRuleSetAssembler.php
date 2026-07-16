<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\LazyMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\ObjectMatchingRuleSet;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMatchingRuleSet;
use ReflectionProperty;

/** @internal */
final class PropertyMatchingRuleSetAssembler implements MatchingRuleSetAssembler
{
    public function __construct(
        private readonly ObjectMatchingRuleSet $ownerRule,
        private readonly ReflectionProperty $reflectionProperty,
    ) {
    }

    public function assemble(PropertyMatchingRuleSetAssemblerService $service): ?MatchingRule
    {
        $matchingRuleSet = new LazyMatchingRule(
            /** @param LazyMatchingRule<MatchingRule> $lazyMatchingRuleSet */
            function (LazyMatchingRule $lazyMatchingRuleSet) use ($service): ?MatchingRule {
                $object = $this->ownerRule->getValue();

                $propertyRuleSet = new PropertyMatchingRuleSet(
                    $this->ownerRule,
                    $this->getName(),
                    $this->getPropertyValue($object),
                    [$lazyMatchingRuleSet],
                );

                return $service->matchingRulesAssemblerService
                    ->assemble(new PropertyMatchingRulesAssembler($propertyRuleSet, $this->reflectionProperty))
                ;
            },
        );

        return $matchingRuleSet->build()?->getOwner();
    }

    public function getOwnerRule(): ObjectMatchingRuleSet
    {
        return $this->ownerRule;
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
