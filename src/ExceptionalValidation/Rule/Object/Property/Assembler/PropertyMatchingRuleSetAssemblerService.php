<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\MatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;

/**
 * @internal
 *
 * @implements MatchingRuleSetAssemblerService<PropertyMatchingRuleSetAssembler>
 */
final class PropertyMatchingRuleSetAssemblerService implements MatchingRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var MatchingRuleSetAssemblerService<PropertyMatchingRulesAssembler> */
        public readonly MatchingRuleSetAssemblerService $matchingRulesAssemblerService,
    ) {
    }

    /** @param PropertyMatchingRuleSetAssembler $assembler */
    public function assemble(MatchingRuleSetAssembler $assembler): ?MatchingRule
    {
        return $assembler->assemble($this);
    }
}
