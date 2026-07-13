<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Closure;

use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements MatchConditionBlueprint<Throwable>
 */
final class SimpleIfClosureMatchConditionBlueprint implements MatchConditionBlueprint
{
    public function __construct(
        /** @var array{object|class-string,string} */
        private readonly array $if,
    ) {
        Assert::methodExists(...$if);
    }

    public function bind(MatchingRule $rule): ClosureMatchCondition
    {
        $object = $rule->getEnclosingObject();

        if ($this->if[0] === $object::class) {
            $if = [$object, $this->if[1]];
        } else {
            $if = $this->if;
        }

        /** @phpstan-ignore callable.nonCallable */
        return new ClosureMatchCondition($if(...));
    }
}
