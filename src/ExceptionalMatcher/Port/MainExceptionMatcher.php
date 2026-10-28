<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Port;

use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\ObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMatcher<MatchedExceptionList>
 */
final class MainExceptionMatcher implements ExceptionMatcher
{
    /** @api */
    public function __construct(
        /** @var ObjectExceptionMappingBlueprintRegistry<object> */
        private readonly ObjectExceptionMappingBlueprintRegistry $planRegistry,
        private readonly ExceptionUnwrapper $exceptionUnwrapper,
    ) {
    }

    public function match(Throwable $exception, object $object): ?MatchedExceptionList
    {
        $plan = $this->planRegistry->getMappingBlueprint($object::class);

        if (null === $plan) {
            return null;
        }

        $matcher = $plan->bind(object: $object);

        $exceptions = $this->exceptionUnwrapper->unwrap($exception);
        $reciprocal = new ExceptionReciprocal($exceptions);

        if (!$matcher->match($reciprocal)) {
            return null;
        }

        return $reciprocal->getMatchedExceptionList();
    }
}
