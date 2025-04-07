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

use function array_filter;
use function array_values;

/** @internal */
final class CaptureMatchConditionFactory implements MatchConditionFactory
{
    private readonly MatchConditionFactory $matchByCustomConditionFactory;

    public function __construct(
        private readonly ContainerInterface $conditionFactoryRegistry,
        private readonly MatchConditionFactory $matchByClassConditionFactory = new ExceptionClassMatchConditionFactory(),
        private readonly MatchConditionFactory $matchBySourceConditionFactory = new ExceptionOriginMatchConditionFactory(),
        private readonly MatchConditionFactory $matchWithClosureConditionFactory = new ClosureMatchConditionFactory(),
    ) {
        $this->matchByCustomConditionFactory = new DelegatingMatchConditionFactory($this->conditionFactoryRegistry);
    }

    public function getCondition(Capture $capture, CaptureRule $parent): MatchCondition
    {
        $conditions = [];

        $conditions[] = $this->matchByClassConditionFactory->getCondition($capture, $parent);
        $conditions[] = $this->matchBySourceConditionFactory->getCondition($capture, $parent);
        $conditions[] = $this->matchByCustomConditionFactory->getCondition($capture, $parent);
        $conditions[] = $this->matchWithClosureConditionFactory->getCondition($capture, $parent);

        return (new CompositeMatchCondition(array_values(array_filter($conditions))))->compile();
    }
}
