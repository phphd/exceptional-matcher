<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Assembler;

use ArrayIterator;
use PhPhD\ExceptionalMatcher\Rule\CompositeMatchingRule;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @template T of MatchingRuleSetAssembler
 *
 * @implements MatchingRuleSetAssemblerService<T>
 */
final class CompositeRuleSetAssemblerService implements MatchingRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var iterable<MatchingRuleSetAssemblerService<T>> */
        private readonly iterable $assemblers,
    ) {
    }

    /** @param T $assembler */
    public function assemble(MatchingRuleSetAssembler $assembler): ?CompositeMatchingRule
    {
        $rules = new ArrayIterator();

        $ownerRule = $assembler->getOwnerRule();
        Assert::notNull($ownerRule);

        $ruleSet = new CompositeMatchingRule($ownerRule, $rules);

        foreach ($this->assemblers as $a) {
            $innerRuleSet = $a->assemble($assembler);

            if (null !== $innerRuleSet) {
                $rules->append($innerRuleSet);
            }
        }

        if (0 === $rules->count()) {
            return null;
        }

        return $ruleSet;
    }
}
