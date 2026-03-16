<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules;

use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\MatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;

use function is_object;

/**
 * @internal
 *
 * @implements MatchingRuleSetAssemblerService<PropertyMatchingRulesAssembler>
 */
final class PropertyNestedValidObjectRuleAssemblerService implements MatchingRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var MatchingRuleSetAssemblerService<ObjectMatchingRuleSetAssembler> */
        private readonly MatchingRuleSetAssemblerService $objectRuleSetAssemblerService,
    ) {
    }

    /** @param PropertyMatchingRulesAssembler $assembler */
    public function assemble(MatchingRuleSetAssembler $assembler): ?MatchingRule
    {
        $propertyRuleSet = $assembler->getParentRule();
        $propertyValue = $propertyRuleSet->getValue();

        if (!is_object($propertyValue)) {
            return null;
        }

        return $this->objectRuleSetAssemblerService
            ->assemble(new ObjectMatchingRuleSetAssembler($propertyValue, $propertyRuleSet))
        ;
    }
}
