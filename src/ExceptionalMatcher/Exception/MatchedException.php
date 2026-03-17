<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Exception;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\MatchExceptionRule;
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
        /** @var MatchExceptionRule<Throwable> */
        private readonly MatchExceptionRule $rule,
    ) {
    }

    /** @return T */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * @return MatchExceptionRule<Throwable>
     *
     * @internal
     */
    public function getRule(): MatchExceptionRule
    {
        return $this->rule;
    }
}
