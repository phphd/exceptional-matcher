<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;

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
