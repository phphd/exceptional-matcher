<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler;

use PhPhD\ExceptionalValidation\Assembler\Object\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Formatter\ExceptionViolationListFormatter;
use PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException;
use PhPhD\ExceptionalValidation\Model\Exception\Adapter\ThrownException;
use PhPhD\ExceptionalValidation\Model\Exception\ExceptionPackage;

/** @internal */
final class ExceptionalHandler implements ExceptionHandler
{
    public function __construct(
        private readonly ObjectRuleSetAssembler $ruleSetAssembler,
        private readonly ExceptionViolationListFormatter $violationsFormatter,
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

        $capturedExceptions = $exceptionPackage->getCapturedExceptions();

        $violationList = $this->violationsFormatter->formatViolations($capturedExceptions);

        throw new ExceptionalValidationFailedException($message, $violationList, $exception);
    }
}
