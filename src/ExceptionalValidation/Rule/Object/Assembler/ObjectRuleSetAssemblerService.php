<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssemblerService<ObjectRuleSetAssembler>
 */
final readonly class ObjectRuleSetAssemblerService implements CaptureRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var CaptureRuleSetAssemblerService<PropertyRuleSetAssembler> */
        public CaptureRuleSetAssemblerService $propertyRuleSetAssemblerService,
    ) {
    }

    /** @param ObjectRuleSetAssembler $assembler */
    public function assemble(CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        return $assembler->assemble($this);
    }
}
