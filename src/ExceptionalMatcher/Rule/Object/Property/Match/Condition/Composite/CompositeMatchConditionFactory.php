<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite;

use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Throwable;

use function array_filter;
use function array_values;
use function count;

/**
 * @internal
 *
 * @implements MatchConditionFactory<Throwable>
 */
final class CompositeMatchConditionFactory implements MatchConditionFactory
{
    /** @api */
    public function __construct(
        /** @var iterable<MatchConditionFactory<Throwable>> */
        private readonly iterable $factories,
    ) {
    }

    public function getCondition(Catch_ $catch, MatchingRule $owner): ?MatchCondition
    {
        $conditions = [];

        foreach ($this->factories as $factory) {
            $matchCondition = $factory->getCondition($catch, $owner);

            if ($matchCondition instanceof FalseCondition) {
                return $matchCondition;
            }

            $conditions[] = $matchCondition;
        }

        /** @var list<MatchCondition<Throwable>> $conditions */
        $conditions = array_values(array_filter($conditions));

        return match (count($conditions)) {
            0 => null,
            1 => $conditions[0],
            default => new CompositeMatchCondition($conditions),
        };
    }
}
