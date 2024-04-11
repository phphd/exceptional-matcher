<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object\Rules;

use ArrayIterator;
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\PropertyRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Model\Rule\CompositeRuleSet;

use function count;

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
    public function assemble(CaptureRule $parent, CaptureRuleSetAssemblerEnvelope $envelope): ?CompositeRuleSet
    {
        $reflectionClass = $envelope->getReflectionClass();

        if ([] === $reflectionClass->getAttributes(ExceptionalValidation::class)) {
            return null;
        }

        $propertyRules = new ArrayIterator();
        $objectRuleSet = new CompositeRuleSet($parent, $propertyRules);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $propertyEnvelope = new PropertyRuleSetAssemblerEnvelope($reflectionProperty);

            $propertyRuleSet = $this->propertyRuleSetAssembler->assemble($objectRuleSet, $propertyEnvelope);

            if (null !== $propertyRuleSet) {
                $propertyRules->append($propertyRuleSet);
            }
        }

        if (0 === count($propertyRules)) {
            return null;
        }

        return $objectRuleSet;
    }
}
