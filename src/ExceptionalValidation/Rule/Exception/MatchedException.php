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
final readonly class MatchedException
{
    public function __construct(
        /** @var T */
        private Throwable $exception,
        private CaptureExceptionRule $rule,
    ) {
    }

    /** @return T */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /** @internal */
    public function getRule(): CaptureExceptionRule
    {
        return $this->rule;
    }
}
