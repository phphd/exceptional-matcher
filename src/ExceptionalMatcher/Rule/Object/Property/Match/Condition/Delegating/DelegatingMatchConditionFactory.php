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
     * @param ContainerInterface<class-string<MatchCondition<T>>,MatchConditionFactory<T>> $matchConditionFactoryRegistry
     */
    public function __construct(
        private readonly ContainerInterface $matchConditionFactoryRegistry,
    ) {
    }

    public function getCondition(Catch_ $catch, MatchingRule $owner): ?MatchCondition
    {
        $factoryId = $catch->getMatch();

        if (null === $factoryId) {
            return null;
        }

        if (!$this->matchConditionFactoryRegistry->has($factoryId)) {
            throw new LogicException('Condition factory not found: '.$factoryId);
        }

        $matchConditionFactory = $this->matchConditionFactoryRegistry->get($factoryId);

        /** @var MatchConditionFactory<Throwable> $matchConditionFactory */
        return $matchConditionFactory->getCondition($catch, $owner);
    }
}
