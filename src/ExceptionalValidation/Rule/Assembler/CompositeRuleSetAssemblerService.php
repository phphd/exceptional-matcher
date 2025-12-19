<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Rule\CompositeRuleSet;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @template T of CaptureRuleSetAssembler
 *
 * @implements CaptureRuleSetAssemblerService<T>
 */
final class CompositeRuleSetAssemblerService implements CaptureRuleSetAssemblerService
{
    /** @api */
    public function __construct(
        /** @var iterable<CaptureRuleSetAssemblerService<T>> */
        private readonly iterable $assemblers,
    ) {
    }

    /** @param T $assembler */
    public function assemble(CaptureRuleSetAssembler $assembler): ?CompositeRuleSet
    {
        $rules = new ArrayIterator();

        $parentRule = $assembler->getParentRule();
        Assert::notNull($parentRule);

        $ruleSet = new CompositeRuleSet($parentRule, $rules);

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
