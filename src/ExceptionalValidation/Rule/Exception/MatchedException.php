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
final class MatchedException
{
    public function __construct(
        /** @var T */
        private readonly Throwable $exception,
        /** @var CaptureExceptionRule<Throwable> */
        private readonly CaptureExceptionRule $rule,
    ) {
    }

    /** @return T */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * @return CaptureExceptionRule<Throwable>
     *
     * @internal
     */
    public function getRule(): CaptureExceptionRule
    {
        return $this->rule;
    }
}
