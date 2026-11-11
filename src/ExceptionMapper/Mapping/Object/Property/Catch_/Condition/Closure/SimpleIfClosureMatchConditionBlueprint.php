<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Closure;

use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
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

    public function bind(ExceptionMappingNode $node): ClosureMatchCondition
    {
        $object = $node->getLeafObject();

        if ($this->if[0] === $object::class) {
            $if = [$object, $this->if[1]];
        } else {
            $if = $this->if;
        }

        /** @phpstan-ignore callable.nonCallable */
        return new ClosureMatchCondition($if(...));
    }
}
