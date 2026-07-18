<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
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
        private readonly ClassMatchingPlanRegistry $planRegistry,
        private readonly ExceptionUnwrapper $exceptionUnwrapper,
    ) {
    }

    public function match(Throwable $exception, object $message): ?MatchedExceptionList
    {
        $plan = $this->planRegistry->getPlan($message::class);

        if (null === $plan) {
            return null;
        }

        $ruleSet = $plan->bind($message);

        $exceptionList = $this->exceptionUnwrapper->unwrap($exception);

        $reciprocal = new ExceptionReciprocal($exceptionList);

        if (!$ruleSet->process($reciprocal)) {
            return null;
        }

        return $reciprocal->getMatchedExceptionList();
    }
}
