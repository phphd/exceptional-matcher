<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules;

use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssemblerEnvelope;
use PhPhD\ExceptionalValidation\Assembler\Object\IterableOfObjectsRuleSetAssembler;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;
use Symfony\Component\Validator\Constraints\Valid;

use function is_iterable;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssembler<PropertyRulesAssemblerEnvelope>
 */
final class PropertyNestedValidIterableRulesAssembler implements CaptureRuleSetAssembler
{
    public function __construct(
        private readonly IterableOfObjectsRuleSetAssembler $iterableObjectsRuleSetAssembler,
    ) {
    }

    /** @param PropertyRulesAssemblerEnvelope $envelope */
    public function assemble(CaptureRule $parent, CaptureRuleSetAssemblerEnvelope $envelope): ?CaptureRule
    {
        $propertyValue = $parent->getValue();

        if (!is_iterable($propertyValue)) {
            return null;
        }

        $validAttributes = $envelope->getReflectionProperty()->getAttributes(Valid::class);

        if ([] === $validAttributes) {
            return null;
        }

        return $this->iterableObjectsRuleSetAssembler->assemble($propertyValue, $parent);
    }
}
