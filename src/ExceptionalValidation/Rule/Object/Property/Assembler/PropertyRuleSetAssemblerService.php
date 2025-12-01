<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssemblerService<PropertyRuleSetAssembler>
 */
final readonly class PropertyRuleSetAssemblerService implements CaptureRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var CaptureRuleSetAssemblerService<PropertyCaptureRulesAssembler> */
        public CaptureRuleSetAssemblerService $captureListAssemblerService,
    ) {
    }

    /** @param PropertyRuleSetAssembler $assembler */
    public function assemble(CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        return $assembler->assemble($this);
    }
}
