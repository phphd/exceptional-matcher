<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Delegating;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\MatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
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

    public function getCondition(Catch_ $catch, MatchingRule $parent): ?MatchCondition
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
