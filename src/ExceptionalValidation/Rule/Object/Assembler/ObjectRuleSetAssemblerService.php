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
    /**
     * @api
     *
     * @param CaptureRuleSetAssemblerService<PropertyRuleSetAssembler> $propertyRuleSetAssemblerService
     */
    public function __construct(
        private CaptureRuleSetAssemblerService $propertyRuleSetAssemblerService,
    ) {
    }

    public function assembleForMessage(object $message, ?CaptureRule $parentRule = null): ?CaptureRule
    {
        return $this->assemble($parentRule, new ObjectRuleSetAssembler($message));
    }

    /** @param ObjectRuleSetAssembler $assembler */
    public function assemble(?CaptureRule $parentRule, CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        return $assembler->assemble($parentRule, $this->propertyRuleSetAssemblerService);
    }
}
