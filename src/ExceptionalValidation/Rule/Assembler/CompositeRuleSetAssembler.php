<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;

/**
 * @internal
 *
 * @template T of CaptureRuleSetAssemblerEnvelope
 *
 * @implements CaptureRuleSetAssembler<T>
 */
final class CompositeRuleSetAssembler implements CaptureRuleSetAssembler
{
    public function __construct(
        /** @var iterable<CaptureRuleSetAssembler<T>> */
        private readonly iterable $assemblers,
    ) {
    }

    /** @param T $envelope */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssemblerEnvelope $envelope): ?CompositeRuleSet
    {
        $rules = new ArrayIterator();
        $ruleSet = new CompositeRuleSet($parentRule, $rules);

        foreach ($this->assemblers as $assembler) {
            $innerRuleSet = $assembler->assemble($ruleSet, $envelope);

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
