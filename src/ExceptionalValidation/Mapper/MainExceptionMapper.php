<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMapper<MatchedExceptionList>
 */
final readonly class MainExceptionMapper implements ExceptionMapper
{
    /** @api */
    public function __construct(
        /** @var CaptureRuleSetAssemblerService<ObjectRuleSetAssembler> */
        private CaptureRuleSetAssemblerService $ruleSetAssemblerService,
        private ExceptionUnwrapper $exceptionUnwrapper,
    ) {
    }

    public function map(object $message, Throwable $exception): ?MatchedExceptionList
    {
        $ruleSet = $this->ruleSetAssemblerService->assemble(new ObjectRuleSetAssembler($message));

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
