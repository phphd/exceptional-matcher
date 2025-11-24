<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet;
use ReflectionProperty;

/** @internal */
final readonly class PropertyRuleSetAssemblerEnvelope implements CaptureRuleSetAssemblerEnvelope
{
    public function __construct(
        private ReflectionProperty $reflectionProperty,
    ) {
    }

    /** @param CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope> $captureListAssembler */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssembler $captureListAssembler): ?PropertyRuleSet
    {
        /** @var object $object */
        $object = $parentRule->getValue();

        $rules = null;
        $rulesSet = new LazyRuleSet(static function () use (&$rules): CaptureRule {
            /** @var CaptureRule $rules */
            return $rules;
        });

        $propertyRuleSet = new PropertyRuleSet($parentRule, $this->getName(), $this->getValue($object), $rulesSet);
        $propertyRulesEnvelope = new PropertyRulesAssemblerEnvelope($this->reflectionProperty);

        $rules = $captureListAssembler->assemble($propertyRuleSet, $propertyRulesEnvelope);

        if (null === $rules) {
            return null;
        }

        return $propertyRuleSet;
    }

    public function getName(): string
    {
        return $this->reflectionProperty->getName();
    }

    public function getValue(object $message): mixed
    {
        if (!$this->reflectionProperty->isInitialized($message)) {
            return null;
        }

        return $this->reflectionProperty->getValue($message);
    }
}
