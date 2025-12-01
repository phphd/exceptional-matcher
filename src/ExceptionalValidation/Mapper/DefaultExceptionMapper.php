<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper;

use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionPackage;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssemblerService;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMapper<non-empty-list<CapturedException<Throwable>>>
 */
final readonly class DefaultExceptionMapper implements ExceptionMapper
{
    /** @api */
    public function __construct(
        private ObjectRuleSetAssemblerService $ruleSetAssemblerService,
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

        $exceptionPackage = new ExceptionPackage($exceptionList);

        if (!$ruleSet->process($exceptionPackage)) {
            return null;
        }

        return $exceptionPackage->getCapturedExceptionsList();
    }
}
