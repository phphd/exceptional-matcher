<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssemblerService;
use Symfony\Component\Validator\Constraints\Valid;

use function is_object;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssemblerService<PropertyRulesAssembler>
 */
final readonly class PropertyNestedValidObjectRuleAssemblerService implements CaptureRuleSetAssemblerService
{
    public function __construct(
        private ObjectRuleSetAssemblerService $objectRuleSetAssembler,
    ) {
    }

    /** @param PropertyRulesAssembler $assembler */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        $propertyValue = $parentRule->getValue();

        if (!is_object($propertyValue)) {
            return null;
        }

        $validAttributes = $assembler->getReflectionProperty()->getAttributes(Valid::class);

        if ([] === $validAttributes) {
            return null;
        }

        return $this->objectRuleSetAssembler->assembleForMessage($propertyValue, $parentRule);
    }
}
