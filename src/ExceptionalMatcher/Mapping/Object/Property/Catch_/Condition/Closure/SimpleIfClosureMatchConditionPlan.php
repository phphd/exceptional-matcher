<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\ento\Compiler\MatchConditionPlan;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements MatchConditionPlan<Throwable>
 */
final class SimpleIfClosureMatchConditionPlan implements MatchConditionPlan
{
    public function __construct(
        /** @var array{object|class-string,string} */
        private readonly array $if,
    ) {
        Assert::methodExists(...$if);
    }

    public function bind(ExceptionMappingNode $rule): ClosureMatchCondition
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
