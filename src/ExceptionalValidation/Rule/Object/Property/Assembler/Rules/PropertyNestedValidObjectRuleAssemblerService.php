<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
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
    /** @api */
    public function __construct(
        private ObjectRuleSetAssemblerService $objectRuleSetAssemblerService,
    ) {
    }

    /** @param PropertyRulesAssembler $assembler */
    public function assemble(CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        $propertyValue = $assembler->getParentRule()->getValue();

        if (!is_object($propertyValue)) {
            return null;
        }

        $validAttributes = $assembler->getReflectionProperty()->getAttributes(Valid::class);

        if ([] === $validAttributes) {
            return null;
        }

        return $this->objectRuleSetAssemblerService
            ->assemble(new ObjectRuleSetAssembler($propertyValue, $assembler->getParentRule()))
        ;
    }
}
