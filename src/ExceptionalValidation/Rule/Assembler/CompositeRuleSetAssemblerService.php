<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;

/**
 * @internal
 *
 * @template T of CaptureRuleSetAssembler
 *
 * @implements CaptureRuleSetAssemblerService<T>
 */
final readonly class CompositeRuleSetAssemblerService implements CaptureRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var iterable<CaptureRuleSetAssemblerService<T>> */
        private iterable $assemblers,
    ) {
    }

    /** @param T $assembler */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssembler $assembler): ?CompositeRuleSet
    {
        $rules = new ArrayIterator();
        $ruleSet = new CompositeRuleSet($parentRule, $rules);

        foreach ($this->assemblers as $a) {
            $innerRuleSet = $a->assemble($parentRule, $assembler);

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
