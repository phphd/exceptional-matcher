<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler;

use PhPhD\ExceptionalValidation\Assembler\Object\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Formatter\ExceptionViolationsListFormatter;
use PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException;
use Throwable;

/** @internal */
final class ExceptionalHandler implements ExceptionHandler
{
    public function __construct(
        private readonly ObjectRuleSetAssembler $ruleSetAssembler,
        private readonly ExceptionViolationsListFormatter $violationsFormatter,
    ) {
    }

    /**
     * @return never
     *
     * @throws Throwable
     */
    public function capture(object $message, Throwable $exception): void
    {
        $ruleSet = $this->ruleSetAssembler->assemble($message);

        if (null === $ruleSet) {
            throw $exception;
        }

        $caughtExceptions = $ruleSet->capture($exception);

        if ([] === $caughtExceptions) {
            throw $exception;
        }

        $violationList = $this->violationsFormatter->formatViolations($caughtExceptions);

        throw new ExceptionalValidationFailedException($message, $violationList, $exception);
    }
}
