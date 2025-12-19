<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Throwable;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssemblerService<PropertyCaptureRulesAssembler>
 */
final class PropertyCaptureRulesAssemblerService implements CaptureRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var MatchConditionFactory<Throwable> */
        private readonly MatchConditionFactory $conditionFactory,
    ) {
    }

    /** @param PropertyCaptureRulesAssembler $assembler */
    public function assemble(CaptureRuleSetAssembler $assembler): ?CompositeRuleSet
    {
        return $assembler->assembleCaptureRules($this->conditionFactory);
    }
}
