<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List;

use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/** @api */
interface ExceptionListViolationFormatter
{
    /** @param non-empty-list<PropriatedException<Throwable>> $propriatedExceptionList */
    public function format(array $propriatedExceptionList): ConstraintViolationListInterface;
}
