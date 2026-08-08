<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher;

use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
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
        /** @var ObjectExceptionMappingPlanRegistry<object> */
        private readonly ObjectExceptionMappingPlanRegistry $planRegistry,
        private readonly ExceptionUnwrapper $exceptionUnwrapper,
    ) {
    }

    public function match(Throwable $exception, object $message): ?MatchedExceptionList
    {
        $plan = $this->planRegistry->getPlan($message::class);

        if (null === $plan) {
            return null;
        }

        $exceptionList = $this->exceptionUnwrapper->unwrap($exception);

        $reciprocal = new ExceptionReciprocal($exceptionList);

        if (!$plan->bind($message)->match($reciprocal)) {
            return null;
        }

        return $reciprocal->getMatchedExceptionList();
    }
}
