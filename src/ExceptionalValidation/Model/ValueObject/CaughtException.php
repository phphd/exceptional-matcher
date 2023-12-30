<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\ValueObject;

use LogicException;
use PhPhD\ExceptionalValidation\Model\Rules\CaptureExceptionRule;
use Throwable;

/** @api */
final class CaughtException
{
    public function __construct(
        private readonly Throwable $exception,
        private readonly CaptureExceptionRule $captureRule,
    ) {
        $exceptionClass = $this->captureRule->getExceptionClass();

        if (!$this->exception instanceof $exceptionClass) {
            throw new LogicException('Caught exception must match capture attribute exception class');
        }
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }

    public function getCaptureRule(): CaptureExceptionRule
    {
        return $this->captureRule;
    }
}
