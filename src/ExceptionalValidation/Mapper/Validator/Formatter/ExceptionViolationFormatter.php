<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter;

use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\PropriatedExceptionFormatter;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

/**
 * @api
 *
 * @phpstan-template-contravariant TException of Throwable
 *
 * @psalm-template-covariant TException of Throwable (psalm doesn't support contravariant templates)
 *
 * @extends PropriatedExceptionFormatter<TException,ConstraintViolationInterface>
 *
 * @psalm-immutable
 */
interface ExceptionViolationFormatter extends PropriatedExceptionFormatter
{
}
