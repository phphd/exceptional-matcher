<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Assembler;

use Generator;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\CompositeMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\LazyMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\ObjectMatchingRuleSet;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Try_;
use ReflectionClass;

/** @internal */
final class ObjectMatchingRuleSetAssembler implements MatchingRuleSetAssembler
{
    /** @var ReflectionClass<object> */
    private readonly ReflectionClass $reflectionClass;

    public function __construct(
        private readonly object $message,
        private readonly ?MatchingRule $parentRule = null,
    ) {
        $this->reflectionClass = new ReflectionClass($this->message::class);
    }

    public function assemble(ObjectMatchingRuleSetAssemblerService $service): ?MatchingRule
    {
        if (!$this->isMarkedWithAnAttribute()) {
            return null;
        }

        $wrappedRuleSet = new LazyMatchingRule(
            /** @param LazyMatchingRule<CompositeMatchingRule> $lazyWrappedRuleSet */
            function (LazyMatchingRule $lazyWrappedRuleSet) use ($service): CompositeMatchingRule {
                $objectRuleSet = new ObjectMatchingRuleSet(
                    $this->message,
                    $this->parentRule,
                    $lazyWrappedRuleSet,
                );

                return new CompositeMatchingRule(
                    $objectRuleSet,
                    $this->getPropertyRules($objectRuleSet, $service),
                );
            },
        );

        return $wrappedRuleSet->build()?->getParent();
    }

    public function getParentRule(): ?MatchingRule
    {
        return $this->parentRule;
    }

    private function isMarkedWithAnAttribute(): bool
    {
        return [] !== $this->reflectionClass->getAttributes(Try_::class);
    }

    private function getPropertyRules(ObjectMatchingRuleSet $objectRuleSet, ObjectMatchingRuleSetAssemblerService $service): Generator
    {
        foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
            $propertyRuleSet = $service->propertyRuleSetAssemblerService
                ->assemble(new PropertyMatchingRuleSetAssembler($objectRuleSet, $reflectionProperty))
            ;

            if (null !== $propertyRuleSet) {
                yield $propertyRuleSet;
            }
        }
    }
}
