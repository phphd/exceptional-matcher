<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property;

use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Model\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Model\Rule\PropertyRuleSet;
use ReflectionProperty;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssembler<PropertyRuleSetAssemblerEnvelope>
 */
final class PropertyRuleSetAssembler implements CaptureRuleSetAssembler
{
    /** @param CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope> $captureListAssembler */
    public function __construct(
        private readonly CaptureRuleSetAssembler $captureListAssembler,
    ) {
    }

    /** @param PropertyRuleSetAssemblerEnvelope $envelope */
    public function assemble(CaptureRule $parent, CaptureRuleSetAssemblerEnvelope $envelope): ?CaptureRule
    {
        /** @var object $object */
        $object = $parent->getValue();
        $reflectionProperty = $envelope->getReflectionProperty();

        $name = $reflectionProperty->getName();
        $value = $this->getPropertyValue($object, $reflectionProperty);

        /** @var CaptureRule $rules */
        $rules = null;
        $rulesSet = new LazyRuleSet(static function () use (&$rules): CaptureRule {
            return $rules;
        });

        $propertyRuleSet = new PropertyRuleSet($parent, $name, $value, $rulesSet);
        $propertyEnvelope = new PropertyRulesAssemblerEnvelope($reflectionProperty);

        $rules = $this->captureListAssembler->assemble($propertyRuleSet, $propertyEnvelope);

        if (null === $rules) {
            return null;
        }

        return $propertyRuleSet;
    }

    private function getPropertyValue(object $message, ReflectionProperty $property): mixed
    {
        if (!$property->isInitialized($message)) {
            return null;
        }

        return $property->getValue($message);
    }
}
