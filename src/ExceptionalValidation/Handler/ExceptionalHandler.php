<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler;

use PhPhD\ExceptionalValidation\Assembler\Object\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Formatter\ExceptionViolationsListFormatter;
use PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException;
use PhPhD\ExceptionalValidation\Model\ValueObject\ThrownExceptions;
use Throwable;

/** @internal */
final class ExceptionalHandler implements ExceptionHandler
{
    public function __construct(
        private readonly ObjectRuleSetAssembler $ruleSetAssembler,
        private readonly ExceptionViolationsListFormatter $violationsFormatter,
    ) {
    }

    public function capture(object $message, Throwable $exception): never
    {
        $ruleSet = $this->ruleSetAssembler->assemble($message);

        if (null === $ruleSet) {
            throw $exception;
        }

        $caughtExceptions = $ruleSet->capture(new ThrownExceptions($exception));

        if ([] === $caughtExceptions) {
            throw $exception;
        }

        $violationList = $this->violationsFormatter->formatViolations($caughtExceptions);

        throw new ExceptionalValidationFailedException($message, $violationList, $exception);
    }
}
