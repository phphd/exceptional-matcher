<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\MatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssembler;

/**
 * @internal
 *
 * @implements MatchingRuleSetAssemblerService<ObjectMatchingRuleSetAssembler>
 */
final class ObjectMatchingRuleSetAssemblerService implements MatchingRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var MatchingRuleSetAssemblerService<PropertyMatchingRuleSetAssembler> */
        public readonly MatchingRuleSetAssemblerService $propertyRuleSetAssemblerService,
    ) {
    }

    /** @param ObjectMatchingRuleSetAssembler $assembler */
    public function assemble(MatchingRuleSetAssembler $assembler): ?MatchingRule
    {
        return $assembler->assemble($this);
    }
}
