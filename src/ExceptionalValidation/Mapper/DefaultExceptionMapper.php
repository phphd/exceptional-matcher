<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMapper<non-empty-list<PropriatedException<Throwable>>>
 */
final readonly class DefaultExceptionMapper implements ExceptionMapper
{
    /** @api */
    public function __construct(
        /** @var CaptureRuleSetAssemblerService<ObjectRuleSetAssembler> */
        private CaptureRuleSetAssemblerService $ruleSetAssemblerService,
        private ExceptionUnwrapper $exceptionUnwrapper,
    ) {
    }

    public function map(object $message, Throwable $exception): ?array
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

        return $reciprocal->getPropriatedExceptionList();
    }
}
