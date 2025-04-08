<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite;

use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Class\ExceptionClassMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\ClosureMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Delegating\DelegatingMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\ExceptionOriginMatchConditionFactory;

use Psr\Container\ContainerInterface;

use Throwable;

use function array_filter;
use function array_values;
use function count;

/** @internal */
final class CaptureMatchConditionFactory implements MatchConditionFactory
{
    public function __construct(
        /** @var iterable<MatchConditionFactory> */
        private readonly iterable $factories,
    ) {
    }

    public static function create(?ContainerInterface $conditionFactoryRegistry = null): self
    {
        return new self([
            new ExceptionClassMatchConditionFactory(),
            new ExceptionOriginMatchConditionFactory(),
            DelegatingMatchConditionFactory::create($conditionFactoryRegistry),
            new ClosureMatchConditionFactory(),
        ]);
    }

    /** @return MatchCondition<Throwable> */
    public function getCondition(Capture $capture, CaptureRule $parent): MatchCondition
    {
        $conditions = [];

        foreach ($this->factories as $factory) {
            $conditions[] = $factory->getCondition($capture, $parent);
        }

        /** @var list<MatchCondition<Throwable>> $conditions */
        $conditions = array_values(array_filter($conditions));

        if (count($conditions) === 1) {
            return $conditions[0];
        }

        return new CompositeMatchCondition($conditions);
    }
}
