<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite;

use LogicException;
use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Class\ExceptionClassMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\ClosureMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\ExceptionOriginMatchConditionFactory;
use Psr\Container\ContainerInterface;

use function array_filter;
use function array_values;

/** @internal */
final class CaptureMatchConditionFactory implements MatchConditionFactory
{
    public function __construct(
        private readonly ContainerInterface $conditionFactoryRegistry,
        private readonly MatchConditionFactory $matchByClassConditionFactory = new ExceptionClassMatchConditionFactory(),
        private readonly MatchConditionFactory $matchBySourceConditionFactory = new ExceptionOriginMatchConditionFactory(),
        private readonly MatchConditionFactory $matchWithClosureConditionFactory = new ClosureMatchConditionFactory(),
    ) {
    }

    public function getCondition(Capture $capture, CaptureRule $parent): MatchCondition
    {
        $conditions = [];

        $conditions[] = $this->matchByClassConditionFactory->getCondition($capture, $parent);
        $conditions[] = $this->matchBySourceConditionFactory->getCondition($capture, $parent);
        $conditions[] = $this->getConditionFromRegistry($capture, $parent);
        $conditions[] = $this->matchWithClosureConditionFactory->getCondition($capture, $parent);

        return (new CompositeMatchCondition(array_values(array_filter($conditions))))->compile();
    }

    private function getConditionFromRegistry(Capture $capture, CaptureRule $parent): ?MatchCondition
    {
        $conditionFactoryId = $capture->getCondition();

        if (null === $conditionFactoryId) {
            return null;
        }

        if (!$this->conditionFactoryRegistry->has($conditionFactoryId)) {
            throw new LogicException('Condition factory not found: '.$conditionFactoryId);
        }

        /** @var MatchConditionFactory $conditionFactory */
        $conditionFactory = $this->conditionFactoryRegistry->get($conditionFactoryId);

        return $conditionFactory->getCondition($capture, $parent);
    }
}
