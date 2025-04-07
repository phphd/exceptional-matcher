<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\CaptureExceptionRule;
use Throwable;

/**
 * @api
 *
 * @template-covariant T of Throwable
 */
final class CapturedException
{
    public function __construct(
        /** @var T */
        private readonly Throwable $exception,
        private readonly CaptureExceptionRule $matchedRule,
    ) {
    }

    /** @return T */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /** @internal */
    public function getMatchedRule(): CaptureExceptionRule
    {
        return $this->matchedRule;
    }
}
