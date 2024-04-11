<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\ValueObject;

use PhPhD\ExceptionalValidation\Model\Rule\CaptureExceptionRule;
use Throwable;

/** @api */
final class CaughtException
{
    public function __construct(
        private readonly Throwable $exception,
        private readonly CaptureExceptionRule $captureRule,
    ) {
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
