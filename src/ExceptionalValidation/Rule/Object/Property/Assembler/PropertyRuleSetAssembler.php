<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet;

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
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssemblerEnvelope $envelope): ?PropertyRuleSet
    {
        /** @var object $object */
        $object = $parentRule->getValue();

        $rules = null;
        $rulesSet = new LazyRuleSet(static function () use (&$rules): CaptureRule {
            /** @var CaptureRule $rules */
            return $rules;
        });

        $propertyRuleSet = new PropertyRuleSet($parentRule, $envelope->getName(), $envelope->getValue($object), $rulesSet);
        $propertyEnvelope = new PropertyRulesAssemblerEnvelope($envelope->getReflectionProperty());

        $rules = $this->captureListAssembler->assemble($propertyRuleSet, $propertyEnvelope);

        if (null === $rules) {
            return null;
        }

        return $propertyRuleSet;
    }
}
