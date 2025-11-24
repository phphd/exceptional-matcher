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
use ReflectionProperty;

use function array_map;

/** @internal */
final readonly class ObjectRulesAssemblerEnvelope implements CaptureRuleSetAssemblerEnvelope
{
    public function __construct(
        /** @var ReflectionClass<object> */
        private ReflectionClass $reflectionClass,
    ) {
    }

    /** @return ReflectionClass<object> */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->reflectionClass;
    }

    /**
     * @param CaptureRuleSetAssembler<PropertyRuleSetAssemblerEnvelope> $propertyRuleSetAssembler
     *
     * @internal
     */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssembler $propertyRuleSetAssembler): ?CaptureRule
    {
        if (!$this->isMarkedWithAnAttribute()) {
            return null;
        }

        return new LazyRuleSet(fn (LazyRuleSet $ruleSet): CompositeRuleSet => new CompositeRuleSet(
            $parentRule,
            $this->getPropertyRules($ruleSet, $propertyRuleSetAssembler),
        ));
    }

    private function isMarkedWithAnAttribute(): bool
    {
        return [] !== $this->reflectionClass->getAttributes(ExceptionalValidation::class);
    }

    /** @param CaptureRuleSetAssembler<PropertyRuleSetAssemblerEnvelope> $propertyRuleSetAssembler */
    private function getPropertyRules(CaptureRule $objectRuleSet, CaptureRuleSetAssembler $propertyRuleSetAssembler): Generator
    {
        foreach ($this->getPropertyEnvelopes() as $propertyRuleSetAssemblerEnvelope) {
            $propertyRuleSet = $propertyRuleSetAssembler->assemble($objectRuleSet, $propertyRuleSetAssemblerEnvelope);

            if (null !== $propertyRuleSet) {
                yield $propertyRuleSet;
            }
        }
    }

    /** @return list<PropertyRuleSetAssemblerEnvelope> */
    private function getPropertyEnvelopes(): array
    {
        return array_map(
            static fn (ReflectionProperty $property): PropertyRuleSetAssemblerEnvelope => new PropertyRuleSetAssemblerEnvelope($property),
            $this->reflectionClass->getProperties(),
        );
    }
}
