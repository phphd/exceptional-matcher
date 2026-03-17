<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher;

use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
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
        /** @var MatchingRuleSetAssemblerService<ObjectMatchingRuleSetAssembler> */
        private readonly MatchingRuleSetAssemblerService $ruleSetAssemblerService,
        private readonly ExceptionUnwrapper $exceptionUnwrapper,
    ) {
    }

    public function match(Throwable $exception, object $message): ?MatchedExceptionList
    {
        $ruleSet = $this->ruleSetAssemblerService->assemble(new ObjectMatchingRuleSetAssembler($message));

        if (null === $ruleSet) {
            return null;
        }

        $exceptionList = $this->exceptionUnwrapper->unwrap($exception);

        $reciprocal = new ExceptionReciprocal($exceptionList);

        if (!$ruleSet->process($reciprocal)) {
            return null;
        }

        return $reciprocal->getMatchedExceptionList();
    }
}
