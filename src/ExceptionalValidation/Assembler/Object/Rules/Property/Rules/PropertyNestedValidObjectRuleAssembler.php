<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules;

use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Assembler\Object\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;
use Symfony\Component\Validator\Constraints\Valid;

use function is_object;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope>
 */
final class PropertyNestedValidObjectRuleAssembler implements CaptureRuleSetAssembler
{
    public function __construct(
        private readonly ObjectRuleSetAssembler $objectTreeAssembler,
    ) {
    }

    /** @param PropertyRulesAssemblerEnvelope $envelope */
    public function assemble(CaptureRule $parent, CaptureRuleSetAssemblerEnvelope $envelope): ?CaptureRule
    {
        $propertyValue = $parent->getValue();

        if (!is_object($propertyValue)) {
            return null;
        }

        $validAttributes = $envelope->getReflectionProperty()->getAttributes(Valid::class);

        if ([] === $validAttributes) {
            return null;
        }

        return $this->objectTreeAssembler->assemble($propertyValue, $parent);
    }
}
