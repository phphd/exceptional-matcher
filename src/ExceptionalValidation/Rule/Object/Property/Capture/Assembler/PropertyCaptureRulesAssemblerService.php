<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssemblerService<PropertyRulesAssembler>
 */
final readonly class PropertyCaptureRulesAssemblerService implements CaptureRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        private MatchConditionFactory $conditionFactory,
    ) {
    }

    /** @param PropertyRulesAssembler $assembler */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssembler $assembler): ?CompositeRuleSet
    {
        return $assembler->assembleCaptureRules($parentRule, $this->conditionFactory);
    }
}
