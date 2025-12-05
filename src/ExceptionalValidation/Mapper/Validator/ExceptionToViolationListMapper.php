<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator;

use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\PropriatedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedExceptionList;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMapper<ConstraintViolationListInterface>
 */
final readonly class ExceptionToViolationListMapper implements ExceptionMapper
{
    /** @api */
    public function __construct(
        /** @var ExceptionMapper<PropriatedExceptionList> */
        private ExceptionMapper $mapper,
        /** @var PropriatedExceptionFormatter<Throwable,ConstraintViolationInterface> */
        private PropriatedExceptionFormatter $formatter,
    ) {
    }

    public function map(object $message, Throwable $exception): ?ConstraintViolationListInterface
    {
        $propriatedExceptionList = $this->mapper->map($message, $exception);

        if (null === $propriatedExceptionList) {
            return null;
        }

        $violations = $propriatedExceptionList->format($this->formatter);

        return new ConstraintViolationList($violations);
    }
}
