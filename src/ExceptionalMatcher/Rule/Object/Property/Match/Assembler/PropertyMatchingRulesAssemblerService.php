<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler;

use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\CompositeMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Throwable;

/**
 * @internal
 *
 * @implements MatchingRuleSetAssemblerService<PropertyMatchingRulesAssembler>
 */
final class PropertyMatchingRulesAssemblerService implements MatchingRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var MatchConditionCompiler<Throwable> */
        public readonly MatchConditionCompiler $matchConditionCompiler,
    ) {
    }

    /** @param PropertyMatchingRulesAssembler $assembler */
    public function assemble(MatchingRuleSetAssembler $assembler): ?CompositeMatchingRule
    {
        return $assembler->assemble($this);
    }
}
