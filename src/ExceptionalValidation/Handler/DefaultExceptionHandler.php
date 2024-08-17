<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler;

use PhPhD\ExceptionalValidation\Assembler\Object\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Formatter\ExceptionListViolationFormatter;
use PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException;
use PhPhD\ExceptionalValidation\Model\Exception\Adapter\ThrownException;
use PhPhD\ExceptionalValidation\Model\Exception\ExceptionPackage;

/** @internal */
final class DefaultExceptionHandler implements ExceptionHandler
{
    public function __construct(
        private readonly ObjectRuleSetAssembler $ruleSetAssembler,
        private readonly ExceptionListViolationFormatter $violationListFormatter,
    ) {
    }

    public function capture(object $message, ThrownException $exception): void
    {
        $ruleSet = $this->ruleSetAssembler->assemble($message);

        if (null === $ruleSet) {
            return;
        }

        $exceptionPackage = new ExceptionPackage($exception);

        if (!$ruleSet->process($exceptionPackage)) {
            return;
        }

        $capturedExceptionList = $exceptionPackage->getCapturedExceptionsList();

        $violationList = $this->violationListFormatter->format($capturedExceptionList);

        throw new ExceptionalValidationFailedException($message, $violationList, $exception);
    }
}
