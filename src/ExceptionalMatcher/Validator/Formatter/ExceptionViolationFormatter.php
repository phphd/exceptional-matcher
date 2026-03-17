<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter;

use PhPhD\ExceptionalMatcher\Rule\Exception\Formatter\MatchedExceptionFormatter;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

/**
 * @api
 *
 * @phpstan-template-contravariant TException of Throwable
 *
 * @psalm-template TException of mixed (psalm doesn't support contravariant templates)
 *
 * @extends MatchedExceptionFormatter<TException,ConstraintViolationInterface>
 */
interface ExceptionViolationFormatter extends MatchedExceptionFormatter
{
}
