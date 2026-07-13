<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Delegating;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<Throwable>
 */
final class DelegatingMatchConditionCompiler implements MatchConditionCompiler
{
    /**
     * @api
     *
     * @template T of Throwable
     *
     * @param ContainerInterface<class-string<MatchCondition<T>>,MatchConditionCompiler<T>> $matchConditionCompilerRegistry
     */
    public function __construct(
        private readonly ContainerInterface $matchConditionCompilerRegistry,
    ) {
    }

    public function compile(Catch_ $catch): ?MatchConditionBlueprint
    {
        $compilerId = $catch->getMatch();

        if (null === $compilerId) {
            return null;
        }

        if (!$this->matchConditionCompilerRegistry->has($compilerId)) {
            throw new LogicException('Match condition compiler not found for: '.$compilerId);
        }

        $matchConditionCompiler = $this->matchConditionCompilerRegistry->get($compilerId);

        /** @var MatchConditionCompiler<Throwable> $matchConditionCompiler */
        return $matchConditionCompiler->compile($catch);
    }
}
