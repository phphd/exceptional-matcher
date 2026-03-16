<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CompositeMatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchConditionFactory;
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
        /** @var MatchConditionFactory<Throwable> */
        private readonly MatchConditionFactory $conditionFactory,
    ) {
    }

    /** @param PropertyMatchingRulesAssembler $assembler */
    public function assemble(MatchingRuleSetAssembler $assembler): ?CompositeMatchingRule
    {
        return $assembler->assembleRules($this->conditionFactory);
    }
}
