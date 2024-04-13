<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object\Rules;

use Generator;
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\PropertyRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Model\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Model\Rule\LazyRuleSet;
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
    public function assemble(CaptureRule $parent, CaptureRuleSetAssemblerEnvelope $envelope): ?CaptureRule
    {
        $reflectionClass = $envelope->getReflectionClass();

        if ([] === $reflectionClass->getAttributes(ExceptionalValidation::class)) {
            return null;
        }

        return new LazyRuleSet(function (LazyRuleSet $ruleSet) use ($parent, $reflectionClass): CompositeRuleSet {
            $propertyRules = $this->getPropertyRules($reflectionClass, $ruleSet);

            return new CompositeRuleSet($parent, $propertyRules);
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
