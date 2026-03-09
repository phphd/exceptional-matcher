<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite;

use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
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

    public function getCondition(Capture $capture, CaptureRule $parent): ?MatchCondition
    {
        $conditions = [];

        foreach ($this->factories as $factory) {
            $matchCondition = $factory->getCondition($capture, $parent);

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
