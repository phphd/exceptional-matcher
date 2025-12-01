<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssembler;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssemblerService<PropertyRuleSetAssembler>
 */
final readonly class PropertyRuleSetAssemblerService implements CaptureRuleSetAssemblerService
{
    /**
     * @api
     *
     * @param CaptureRuleSetAssemblerService<PropertyRulesAssembler> $captureListAssemblerService
     */
    public function __construct(
        private CaptureRuleSetAssemblerService $captureListAssemblerService,
    ) {
    }

    /** @param PropertyRuleSetAssembler $assembler */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        return $assembler->assemble($parentRule, $this->captureListAssemblerService);
    }
}
