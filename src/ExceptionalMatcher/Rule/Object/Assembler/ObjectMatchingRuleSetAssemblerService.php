<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Assembler;

use Closure;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssembler;

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
        private ?Closure $autoloadClassNames,
    ) {
    }

    /** @param ObjectMatchingRuleSetAssembler $assembler */
    public function assemble(MatchingRuleSetAssembler $assembler): ?MatchingRule
    {
        $rule = $assembler->assemble($this);

        if (null !== $this->autoloadClassNames && null !== $rule) {
            $this->autoloadClassNames->__invoke();
            $this->autoloadClassNames = null;
        }

        return $rule;
    }
}
