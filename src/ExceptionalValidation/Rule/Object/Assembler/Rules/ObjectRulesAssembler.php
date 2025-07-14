<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules;

use Generator;
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssemblerEnvelope;
use ReflectionClass;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssembler< ObjectRulesAssemblerEnvelope>
 */
final class ObjectRulesAssembler implements CaptureRuleSetAssembler
{
    /** @param CaptureRuleSetAssembler<PropertyRuleSetAssemblerEnvelope> $propertyRuleSetAssembler */
    public function __construct(
        private readonly CaptureRuleSetAssembler $propertyRuleSetAssembler,
    ) {
    }

    /** @param ObjectRulesAssemblerEnvelope $envelope */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssemblerEnvelope $envelope): ?CaptureRule
    {
        $reflectionClass = $envelope->getReflectionClass();

        if ([] === $reflectionClass->getAttributes(ExceptionalValidation::class)) {
            return null;
        }

        return new LazyRuleSet(function (LazyRuleSet $ruleSet) use ($parentRule, $reflectionClass): CompositeRuleSet {
            $propertyRules = $this->getPropertyRules($reflectionClass, $ruleSet);

            return new CompositeRuleSet($parentRule, $propertyRules);
        });
    }

    /** @param ReflectionClass<object> $reflectionClass */
    private function getPropertyRules(ReflectionClass $reflectionClass, CaptureRule $objectRuleSet): Generator
    {
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyEnvelope = new PropertyRuleSetAssemblerEnvelope($reflectionProperty);

            $propertyRuleSet = $this->propertyRuleSetAssembler->assemble($objectRuleSet, $propertyEnvelope);

            if (null !== $propertyRuleSet) {
                yield $propertyRuleSet;
            }
        }
    }
}
