<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler;

use function is_object;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssemblerService<PropertyCaptureRulesAssembler>
 */
final class PropertyNestedValidObjectRuleAssemblerService implements CaptureRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var CaptureRuleSetAssemblerService<ObjectRuleSetAssembler> */
        private readonly CaptureRuleSetAssemblerService $objectRuleSetAssemblerService,
    ) {
    }

    /** @param PropertyCaptureRulesAssembler $assembler */
    public function assemble(CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        $propertyRuleSet = $assembler->getParentRule();
        $propertyValue = $propertyRuleSet->getValue();

        if (!is_object($propertyValue)) {
            return null;
        }

        return $this->objectRuleSetAssemblerService
            ->assemble(new ObjectRuleSetAssembler($propertyValue, $propertyRuleSet))
        ;
    }
}
