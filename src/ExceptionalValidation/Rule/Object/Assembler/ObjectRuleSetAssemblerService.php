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
     *@api
     *
     * @param CaptureRuleSetAssemblerService<PropertyRuleSetAssembler> $propertyRuleSetAssembler
     */
    public function __construct(
        private CaptureRuleSetAssemblerService $propertyRuleSetAssembler,
    ) {
    }

    public function assembleForMessage(object $message, ?CaptureRule $parentRule = null): ?CaptureRule
    {
        $envelope = ObjectRuleSetAssembler::createForMessage($message);

        if (null === $envelope) {
            return null;
        }

        return $this->assemble($parentRule, $envelope);
    }

    /** @param ObjectRuleSetAssembler $assembler */
    public function assemble(?CaptureRule $parentRule, CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        return $assembler->assemble($parentRule, $this->propertyRuleSetAssembler);
    }
}
