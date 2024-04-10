<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Assembler;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Model\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Model\Rule\CompositeRuleSet;

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
        private readonly iterable $captureListAssemblers,
    ) {
    }

    /** @param T $envelope */
    public function assemble(CaptureRule $parent, CaptureRuleSetAssemblerEnvelope $envelope): ?CompositeRuleSet
    {
        $rules = new ArrayIterator();
        $ruleSet = new CompositeRuleSet($parent, $rules);

        foreach ($this->captureListAssemblers as $captureListAssembler) {
            $innerRuleSet = $captureListAssembler->assemble($ruleSet, $envelope);

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
