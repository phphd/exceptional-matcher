<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules\ObjectRuleSetAssembler;
use Symfony\Component\Validator\Constraints\Valid;

use function is_object;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope>
 */
final readonly class PropertyNestedValidObjectRuleAssembler implements CaptureRuleSetAssembler
{
    public function __construct(
        private ObjectRuleSetAssembler $objectRuleSetAssembler,
    ) {
    }

    /** @param PropertyRulesAssemblerEnvelope $envelope */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssemblerEnvelope $envelope): ?CaptureRule
    {
        $propertyValue = $parentRule->getValue();

        if (!is_object($propertyValue)) {
            return null;
        }

        $validAttributes = $envelope->getReflectionProperty()->getAttributes(Valid::class);

        if ([] === $validAttributes) {
            return null;
        }

        return $this->objectRuleSetAssembler->assembleForMessage($propertyValue, $parentRule);
    }
}
