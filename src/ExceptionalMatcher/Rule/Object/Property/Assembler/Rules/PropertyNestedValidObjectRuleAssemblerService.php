<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\Rules;

use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;

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
