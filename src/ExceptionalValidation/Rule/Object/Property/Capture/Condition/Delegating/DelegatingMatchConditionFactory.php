<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Delegating;

use LogicException;
use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionFactory<Throwable>
 */
final class DelegatingMatchConditionFactory implements MatchConditionFactory
{
    /**
     * @api
     *
     * @template T of Throwable
     *
     * @param ContainerInterface<class-string<MatchCondition<T>>,MatchConditionFactory<T>> $conditionFactoryRegistry
     */
    public function __construct(
        private readonly ContainerInterface $conditionFactoryRegistry,
    ) {
    }

    public function getCondition(Catch_ $catch, CaptureRule $parent): ?MatchCondition
    {
        $conditionFactoryId = $catch->getCondition();

        if (null === $conditionFactoryId) {
            return null;
        }

        if (!$this->conditionFactoryRegistry->has($conditionFactoryId)) {
            throw new LogicException('Condition factory not found: '.$conditionFactoryId);
        }

        $conditionFactory = $this->conditionFactoryRegistry->get($conditionFactoryId);

        /** @var MatchConditionFactory<Throwable> $conditionFactory */
        return $conditionFactory->getCondition($catch, $parent);
    }
}
